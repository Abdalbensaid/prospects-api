import sys
import json
import os
from telethon.sync import TelegramClient

API_ID=22584249
API_HASH='5192e2d943fc80224078909f49625e42'

if len(sys.argv) < 3:
    print(json.dumps({"error": "Lien ou session manquant"}))
    sys.exit(1)

group_link = sys.argv[1]
session_file = sys.argv[2]

session_path = os.path.join(os.path.dirname(__file__), session_file)
client = TelegramClient(session_path, API_ID, API_HASH)

async def main():
    await client.connect()

    if not await client.is_user_authorized():
        print(json.dumps({"error": "Session non autorisée"}))
        return

    try:
        group = await client.get_entity(group_link)
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        return

    members = []
    async for user in client.iter_participants(group, limit=100):
        members.append({
            'id': user.id,
            'username': user.username,
            'first_name': user.first_name,
            'last_name': user.last_name
        })

    print(json.dumps({"status": "success", "members": members}, ensure_ascii=False))
    await client.disconnect()

import asyncio
asyncio.run(main())
