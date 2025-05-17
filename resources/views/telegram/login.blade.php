<form method="POST" action="/telegram/send-code">
    @csrf
    <label>Numéro de téléphone :</label>
    <input type="text" name="phone" required>
    <button type="submit">Recevoir le code</button>
</form>
@if(session('error'))
    <div style="color:red;">{{ session('error') }}</div>
@endif
