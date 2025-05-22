<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription (API)</title>
</head>
<body>
    <h2>Créer un compte</h2>

    <form id="register-form">
        <input type="text" name="name" placeholder="Nom complet" required><br><br>
        <input type="email" name="email" placeholder="Adresse email" required><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        <button type="submit">S'inscrire</button>
    </form>

    <div id="response" style="margin-top:1em;"></div>

    <script>
        const form = document.getElementById('register-form');
        const responseDiv = document.getElementById('response');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const payload = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password')
            };

            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok) {
                localStorage.setItem('token', data.access_token);
                responseDiv.innerHTML = `<span style="color:green;">Inscription réussie !</span>`;
            } else {
                let msg = data.message || "Une erreur est survenue";
                if (data.errors) {
                    msg = Object.values(data.errors).flat().join('<br>');
                }
                responseDiv.innerHTML = `<span style="color:red;">${msg}</span>`;
            }
        });
    </script>
</body>
</html>
