# Hades - Backend
PHP Backend for the **Hades** configuration and distribution utility
# Requirements:
**PHP** 7.1+

**SQLite** PHP extention

**Composer**
# Instalation:
```bash
cd [your public www folder]

curl -O https://github.com/gpjp-hades/Backend/releases/download/0.3/packages.json
composer create-project --repository packages.json gpjp-hades/backend [your target folder]

vi [your target folder]/bootstrap/app.php
# find $config['path'] and set it to [your target folder]
```
