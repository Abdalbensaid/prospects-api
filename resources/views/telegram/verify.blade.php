<form method="POST" action="/telegram/verify">
    @csrf
    <input type="hidden" name="phone" value="{{ $phone }}">
    <label>Code reçu :</label>
    <input type="text" name="code" required>
    <button type="submit">Valider</button>
</form>
@if(session('error'))
    <div style="color:red;">{{ session('error') }}</div>
@endif
