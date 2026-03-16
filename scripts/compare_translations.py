import re
from pathlib import Path


def load_keys(path):
    text = Path(path).read_text(encoding='utf-8')
    pattern = re.compile(r"['\"]([a-zA-Z0-9_\- ]+)['\"]\s*=>")
    return [m.group(1) for m in pattern.finditer(text)]


en_path = Path('Modules/Eshop360/Resources/lang/en/eshop.php')
fr_path = Path('Modules/Eshop360/Resources/lang/fr/eshop.php')

en = load_keys(en_path)
fr = load_keys(fr_path)

missing_in_fr = set(en) - set(fr)
missing_in_en = set(fr) - set(en)

print('Keys en:', len(en), 'fr:', len(fr))
print('missing_in_fr:', len(missing_in_fr))
print('missing_in_en:', len(missing_in_en))
if missing_in_fr:
    print('sample missing in fr:', list(sorted(missing_in_fr))[:20])
if missing_in_en:
    print('sample missing in en:', list(sorted(missing_in_en))[:20])
