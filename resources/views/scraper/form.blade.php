
<div class="container">
    <h1>Scraper un groupe Telegram</h1>

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ url('/scraperform') }}">
        @csrf
        <label for="group">Lien du groupe Telegram :</label>
        <input type="text" name="group" id="group" value="{{ $groupLink ?? '' }}" required style="width: 60%;">
        <button type="submit">Scraper</button>
    </form>


    @if(isset($members))
        <h2>Membres du groupe</h2>
        <table border="1" cellpadding="5">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Prénom</th>
                <th>Nom</th>
            </tr>
            @foreach($members as $member)
                <tr>
                    <td>{{ $member['id'] }}</td>
                    <td>{{ $member['username'] ?? '-' }}</td>
                    <td>{{ $member['first_name'] ?? '-' }}</td>
                    <td>{{ $member['last_name'] ?? '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if(isset($members))
        <h2>Envoyer un message à tous ces membres</h2>
        <form method="POST" action="{{ url('/send-messages') }}">
            @csrf
            <label>Message :</label><br>
            <textarea name="message" rows="4" cols="60" required></textarea><br>
            <input type="hidden" name="groupLink" value="{{ $groupLink }}">
            <button type="submit">Envoyer</button>
        </form>
    @endif

</div>
