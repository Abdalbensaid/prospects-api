import sys
import os
import json
import asyncio
from telethon.sync import TelegramClient

API_ID = 22584249
API_HASH = '5192e2d943fc80224078909f49625e42'

if len(sys.argv) < 3:
    print("Usage: send_message.py <message> <session_file>")
    sys.exit(1)

message = sys.argv[1]
session_file = sys.argv[2]

session_path = os.path.join(os.path.dirname(__file__), session_file)
print(json.dumps({"debug": f"session_path used = {session_path}"}))

client = TelegramClient(session_path, API_ID, API_HASH)

async def main():
    await client.connect()
    if not await client.is_user_authorized():
        print("Session non autorisée.")
        return

    usernames_path = os.path.join(os.path.dirname(__file__), "usernames.json")

    try:
        with open(usernames_path, "r") as f:
            usernames = json.load(f)
    except FileNotFoundError:
        print("Erreur : fichier usernames.json introuvable.")
        return

    for username in usernames:
        try:
            await client.send_message(username, message)
            print(f"✅ Envoyé à @{username}")
        except Exception as e:
            print(f"❌ Échec pour @{username}: {str(e)}")

    await client.disconnect()

asyncio.run(main())
