"""Builds factorh.co into site/: copies static/ and fills src/templates/* with content/*.yml.

Template slots:
  {{t:grupo.clave}}         a text, as HTML (see text() for the markup it understands)
  {{t:grupo.clave|<span…>}} same, and [[…]] in the text becomes that span
  {{a:grupo.clave}}         a text inside an attribute (alt, aria-label, meta content…)
  {{img:grupo.clave}}       a photo path, e.g. img/rina.jpg
A key that starts with "site." is read from content/site.yml, anything else from the page's own file.
Run: python3 src/build.py   (needs pyyaml)
"""
import html, pathlib, re, shutil, sys, yaml

ROOT = pathlib.Path(__file__).resolve().parent.parent
OUT = ROOT / "site"

def load(name):
    return yaml.safe_load((ROOT / "content" / f"{name}.yml").read_text(encoding="utf-8")) or {}

def text(v, span=None):
    """Content text -> HTML: escapes it, then **negrita**, _cursiva_, [[resaltado]] and line breaks."""
    v = html.escape(str(v).strip(), quote=False)
    v = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", v, flags=re.S)
    v = re.sub(r"(?<!\w)_(.+?)_(?!\w)", r"<em>\1</em>", v, flags=re.S)
    v = re.sub(r"\[\[(.+?)\]\]", lambda m: (span or "<span>") + m.group(1) + "</span>", v, flags=re.S)
    return v.replace("\n", "<br>")

def fill(tpl, page, site, name):
    def get(path):
        src, path = (site, path[5:]) if path.startswith("site.") else (page, path)
        group, key = path.split(".", 1)
        try:
            return src[group][key]
        except (KeyError, TypeError):
            sys.exit(f"{name}: falta el texto '{path}' en content/{'site' if src is site else name}.yml")
    tpl = re.sub(r"\{\{t:([\w.-]+)(?:\|(<span[^}]*>))?\}\}", lambda m: text(get(m.group(1)), m.group(2)), tpl)
    tpl = re.sub(r"\{\{a:([\w.-]+)\}\}", lambda m: html.escape(str(get(m.group(1))).strip()), tpl)
    tpl = re.sub(r"\{\{img:([\w.-]+)\}\}", lambda m: html.escape(str(get(m.group(1))).strip().lstrip("/")), tpl)
    left = re.search(r"\{\{[^}]*\}\}", tpl)
    if left: sys.exit(f"{name}: no entiendo {left.group(0)}")
    return tpl

def main():
    if OUT.exists(): shutil.rmtree(OUT)
    shutil.copytree(ROOT / "static", OUT)
    site = load("site")
    for tpl in sorted((ROOT / "src/templates").iterdir()):
        name = tpl.stem
        page = load(name) if (ROOT / "content" / f"{name}.yml").exists() else {}
        (OUT / tpl.name).write_text(fill(tpl.read_text(encoding="utf-8"), page, site, name), encoding="utf-8")
    print(f"listo: {OUT}")

if __name__ == "__main__":
    main()
