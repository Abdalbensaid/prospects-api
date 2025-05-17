<h1>Comptes Telegram disponibles</h1>

<ul>
    @foreach($accounts as $account)
        <li>
            {{ $account->phone }}
            <form method="POST" action="/scrape-now" style="display:inline;">
                @csrf
                <input type="hidden" name="phone" value="{{ $account->phone }}">
                <button type="submit">Scraper avec ce compte</button>
            </form>
        </li>
    @endforeach
</ul>