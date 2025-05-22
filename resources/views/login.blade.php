<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion (API)</title>
</head>
<body>
    <h2>Connexion</h2>

    <form id="login-form">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        <button type="submit">Se connecter</button>
    </form>

    <div id="response" style="margin-top:1em;"></div>
    <a href="/registe">S'enregistrer</a>

    <script>
        const form = document.getElementById('login-form');
        const responseDiv = document.getElementById('response');

        // Si déjà connecté avec un token => vérifier sessions
        const existingToken = localStorage.getItem('token');
        if (existingToken) {
            fetch('/sessions', {
                headers: {
                    'Authorization': `Bearer ${existingToken}`,
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (res.ok) return res.json();
                throw new Error('Session non trouvée');
            })
            .then(data => {
                if (data && data.length > 0) {
                    window.location.href = '/scraper-form';
                } else {
                    window.location.href = '/telegram/login';
                }
            })
            .catch(err => {
                console.warn('Aucune session trouvée ou token expiré');
            });
        }

        // Formulaire de connexion
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const res = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: formData.get('email'),
                        password: formData.get('password')
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    localStorage.setItem('token', data.access_token);
                    responseDiv.innerHTML = `<span style="color:green;">Connexion réussie. Redirection...</span>`;

                    // Vérifie s'il a une session Telegram après login
                    const sessionCheck = await fetch('/sessions', {
                        headers: {
                            'Authorization': `Bearer ${data.access_token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const sessionData = await sessionCheck.json();

                    setTimeout(() => {
                        if (sessionCheck.ok && sessionData.length > 0) {
                            window.location.href = '/';
                        } else {
                            window.location.href = '/telegram/login';
                        }
                    }, 1000);

                } else {
                    responseDiv.innerHTML = `<span style="color:red;">${data.message || 'Identifiants incorrects.'}</span>`;
                }

            } catch (error) {
                console.error(error);
                responseDiv.innerHTML = `<span style="color:red;">Erreur réseau ou serveur</span>`;
            }
        });
    </script>
</body>
</html>
