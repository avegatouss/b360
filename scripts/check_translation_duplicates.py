import re
from pathlib import Path

path = Path('Modules/Eshop360/Resources/lang/en/eshop.php')
text = path.read_text(encoding='utf-8')
keys = re.findall(r"['\"]([a-zA-Z0-9_]+)['\"]\s*=>", text)
dups = [k for k in set(keys) if keys.count(k) > 1]
print('duplicate keys count', len(dups))
if dups:
    print('\n'.join(sorted(dups)))
