import sys
import os
import json
from telethon.sync import TelegramClient
from telethon.tl.functions.messages import SendMessageRequest

API_ID = 22584249
API_HASH = '5192e2d943fc80224078909f49625e42'

if len(sys.argv) < 3:
    print("Usage: send_message.py <message> <session_file>")
    sys.exit(1)

message = sys.argv[1]
session_file = sys.argv[2]
session_path = os.path.join(os.path.dirname(__file__), session_file)

client = TelegramClient(session_path, API_ID, API_HASH)

async def main():
    await client.connect()
    if not await client.is_user_authorized():
        print("Session non autorisée.")
        return

    # Chargement depuis un fichier temporaire ou table Laravel (à améliorer)
    usernames = []  # Liste des usernames à charger

    # Simulation : lire depuis un fichier json généré temporairement
    with open("usernames.json", "r") as f:
        usernames = json.load(f)

    for username in usernames:
        try:
            await client.send_message(username, message)
        except Exception as e:
            print(f"Erreur en envoyant à @{username}: {str(e)}")

    await client.disconnect()

import asyncio
asyncio.run(main())
