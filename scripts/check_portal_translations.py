import re
from pathlib import Path

portal_dir = Path('Modules/Eshop360/Resources/views')
file_paths = list(portal_dir.rglob('*.blade.php'))

keys=set()
for path in portal_dir.rglob('*.blade.php'):
    text = path.read_text(encoding='utf-8')
    for m in re.finditer(r"__\(\s*'eshop360::eshop\.([a-zA-Z0-9_]+)'", text):
        keys.add(m.group(1))
    for m in re.finditer(r'__\(\s*\"eshop360::eshop\.([a-zA-Z0-9_]+)\"', text):
        keys.add(m.group(1))

text = Path('Modules/Eshop360/Resources/lang/en/eshop.php').read_text(encoding='utf-8')
trans_keys = set(re.findall(r"['\"]([a-zA-Z0-9_]+)['\"]\s*=>", text))
missing = sorted([k for k in keys if k not in trans_keys])
print('keys used:', len(keys))
print('missing in en:', len(missing))
for k in missing:
    print(k)
