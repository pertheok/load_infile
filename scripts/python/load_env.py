import os

ENV_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '..', 'config', '.env')


def load_env(path=ENV_PATH):
    """Load KEY=value pairs from config/.env into os.environ. Existing environment variables win."""
    if not os.path.isfile(path):
        raise FileNotFoundError(f"Env file not found: {path}")
    with open(path, encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            name, value = line.split('=', 1)
            os.environ.setdefault(name.strip(), value.strip())


def db_connection_str(prefix='DB'):
    """Build a SQLAlchemy MySQL URL from <prefix>_HOST/_PORT/_USER/_PASS/_NAME."""
    from urllib.parse import quote_plus
    load_env()
    e = os.environ
    return (f"mysql+pymysql://{quote_plus(e[f'{prefix}_USER'])}:{quote_plus(e[f'{prefix}_PASS'])}"
            f"@{e[f'{prefix}_HOST']}:{e.get(f'{prefix}_PORT', '3306')}/{e[f'{prefix}_NAME']}")


load_env()
