<button id="logout-btn" style="margin-top: 1em;">Se déconnecter</button>

<div class="container">
    <h1>Scraper un groupe Telegram</h1>

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ url('/scraperform') }}">
        @csrf

        <label for="group">Lien du groupe Telegram :</label>
        <input type="text" name="group" id="group" value="{{ $groupLink ?? '' }}" required style="width: 60%;"><br><br>

        <label for="account_id">Choisir un compte Telegram :</label>
        <select name="account_id" id="account_id" required>
            @if(isset($accounts) && count($accounts) > 0)
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">
                        {{ $account->phone }}
                    </option>
                @endforeach
            @else
                <option disabled selected>Aucun compte connecté</option>
            @endif
        </select><br><br>

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
<script>
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('token');
            window.location.href = '/loginshow';
        });
    }
    if (!localStorage.getItem('token')) {
        const btn = document.getElementById('logout-btn');
        if (btn) btn.style.display = 'none';
    }
</script>
