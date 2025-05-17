import sys
from telethon.sync import TelegramClient

API_ID = 22584249
API_HASH = '5192e2d943fc80224078909f49625e42'

if len(sys.argv) < 2:
    print("Numéro manquant")
    sys.exit(1)

phone = sys.argv[1]
session_name = phone.replace("+", "").replace(" ", "")

client = TelegramClient(session_name, API_ID, API_HASH)
client.connect()

if not client.is_user_authorized():
    client.send_code_request(phone)
    print("Code envoyé")
else:
    print("Déjà connecté")

client.disconnect()
