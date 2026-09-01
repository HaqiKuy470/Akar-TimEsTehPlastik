#!/usr/bin/env python3
"""
Pengunduh dataset Portal Satu Data Kemendikdasmen.

Contoh pakai:
    python3 unduh.py --list
    python3 unduh.py --topik "Rapor Pendidikan Indonesia"
    python3 unduh.py --topik "Asesmen Nasional" --tahun 2025
    python3 unduh.py --cari "anak tidak sekolah"

Catatan: API membatasi 20 dataset per request, jadi katalog dipaginasi.
"""
import argparse, json, os, re, sys, time, urllib.request
from collections import Counter

API = "https://api.data.belajar.id/data-portal-backend"
UA = {"User-Agent": "Mozilla/5.0", "Accept": "application/json"}
CACHE = "katalog_cache.json"


def get_json(url, timeout=45):
    return json.load(urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=timeout))


def ambil_katalog(pakai_cache=True):
    if pakai_cache and os.path.exists(CACHE):
        return json.load(open(CACHE, encoding="utf-8"))
    items, offset = [], 0
    while True:
        d = get_json(f"{API}/v2/datasets?limit=20&offset={offset}")
        batch = d.get("data") or []
        if not batch:
            break
        items += batch
        offset += 20
        total = d["meta"]["total"]
        print(f"\r  katalog {min(offset, total)}/{total}", end="", file=sys.stderr)
        if offset >= total:
            break
        time.sleep(0.2)
    print(file=sys.stderr)
    json.dump(items, open(CACHE, "w", encoding="utf-8"), ensure_ascii=False)
    return items


def slug(s):
    return re.sub(r"[^a-zA-Z0-9._-]+", "_", s)[:90].strip("_")


def topik_str(it):
    return ", ".join(t["nama"] for t in (it.get("topik") or [])) or "lain"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--list", action="store_true", help="tampilkan ringkasan topik")
    ap.add_argument("--topik", help="filter berdasarkan nama topik (sebagian cocok)")
    ap.add_argument("--tahun", type=int, help="filter tahun")
    ap.add_argument("--cari", help="cari kata kunci di judul/deskripsi")
    ap.add_argument("--out", default="unduhan", help="folder tujuan")
    ap.add_argument("--refresh", action="store_true", help="abaikan cache katalog")
    a = ap.parse_args()

    items = ambil_katalog(pakai_cache=not a.refresh)
    print(f"{len(items)} dataset di katalog", file=sys.stderr)

    if a.list:
        c = Counter(t["nama"] for it in items for t in (it.get("topik") or []))
        print("\nTOPIK:")
        for k, v in c.most_common():
            print(f"{v:5d}  {k}")
        print("\nTAHUN:", dict(sorted(Counter(i["tahun"] for i in items).items())))
        return

    sel = items
    if a.topik:
        sel = [i for i in sel if a.topik.lower() in topik_str(i).lower()]
    if a.tahun:
        sel = [i for i in sel if i["tahun"] == a.tahun]
    if a.cari:
        q = a.cari.lower()
        sel = [i for i in sel if q in i["judul"].lower() or q in (i.get("deskripsi") or "").lower()]

    files = [(i, f) for i in sel for f in (i.get("file") or [])]
    if not files:
        print("Tidak ada file yang cocok dengan filter.", file=sys.stderr)
        return

    total_mb = sum(int(f.get("fileSizeBytes") or 0) for _, f in files) / 1048576
    print(f"{len(sel)} dataset, {len(files)} file (metadata ~{total_mb:.1f} MB, "
          f"ukuran asli bisa jauh lebih besar)", file=sys.stderr)

    os.makedirs(a.out, exist_ok=True)
    for n, (i, f) in enumerate(files, 1):
        folder = os.path.join(a.out, slug(topik_str(i)))
        os.makedirs(folder, exist_ok=True)
        path = os.path.join(folder, f"{i['tahun']}_{slug(i['slug'])}.{f['tipe']}")
        if os.path.exists(path) and os.path.getsize(path) > 0:
            print(f"[{n}/{len(files)}] lewati {os.path.basename(path)}")
            continue
        try:
            req = urllib.request.Request(f["url"], headers={"User-Agent": "Mozilla/5.0"})
            with urllib.request.urlopen(req, timeout=180) as r, open(path, "wb") as out:
                while True:
                    chunk = r.read(1 << 20)
                    if not chunk:
                        break
                    out.write(chunk)
            print(f"[{n}/{len(files)}] OK {os.path.basename(path)} "
                  f"({os.path.getsize(path)/1048576:.1f} MB)")
        except Exception as e:
            print(f"[{n}/{len(files)}] GAGAL {i['slug']}: {e}", file=sys.stderr)
        time.sleep(0.2)


if __name__ == "__main__":
    main()
