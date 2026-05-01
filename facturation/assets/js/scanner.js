// Scanner de codes-barres avec ZXing
let codeReader = null;
let selectedDeviceId = null;

function demarrerScanner() {
    // Arrêter le scanner existant
    if (codeReader) {
        codeReader.reset();
    }
    
    codeReader = new ZXing.BrowserMultiFormatReader();
    
    // Lister les caméras
    codeReader.listVideoInputDevices()
        .then(videoInputDevices => {
            if (videoInputDevices.length === 0) {
                console.error('Aucune caméra trouvée');
                return;
            }
            
            // Utiliser la caméra arrière si disponible, sinon la première
            const backCamera = videoInputDevices.find(device => 
                device.label.toLowerCase().includes('back') || 
                device.label.toLowerCase().includes('environment')
            );
            selectedDeviceId = backCamera ? backCamera.deviceId : videoInputDevices[0].deviceId;
            
            // Démarrer le scan
            return codeReader.decodeFromVideoDevice(selectedDeviceId, 'video-preview', (result, err) => {
                if (result) {
                    // Code-barres détecté
                    const codeBarre = result.getText();
                    console.log('Code-barres scanné:', codeBarre);
                    
                    // Envoyer au serveur
                    traiterCodeBarre(codeBarre);
                    
                    // Arrêter le scanner après capture
                    arreterScanner();
                }
                if (err && !err.message.includes('NotFound')) {
                    console.error(err);
                }
            });
        })
        .catch(err => {
            console.error('Erreur caméra:', err);
            document.getElementById('scan-status').innerHTML = 
                '<p class="error">Impossible d\'accéder à la caméra. Vérifiez les permissions.</p>';
        });
}

function arreterScanner() {
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
}

function traiterCodeBarre(codeBarre) {
    // Afficher le code en cours de traitement
    document.getElementById('scan-status').innerHTML = 
        '<p>Code scanné : ' + codeBarre + ' - Vérification...</p>';
    
    // Envoyer au serveur via AJAX
    fetch('/facturation/modules/produits/lire.php?code_barre=' + encodeURIComponent(codeBarre))
        .then(response => response.json())
        .then(data => {
            if (data.trouve) {
                // Produit existant
                document.getElementById('produit-info').innerHTML = `
                    <div class="success">
                        <h3>Produit trouvé</h3>
                        <p><strong>Nom:</strong> ${data.produit.nom}</p>
                        <p><strong>Prix HT:</strong> ${data.produit.prix_unitaire_ht} CDF</p>
                        <p><strong>Stock:</strong> ${data.produit.quantite_stock}</p>
                        <p><strong>Date expiration:</strong> ${data.produit.date_expiration}</p>
                    </div>
                `;
                document.getElementById('code-barre-recupere').value = codeBarre;
                document.getElementById('formulaire-produit').style.display = 'none';
            } else {
                // Nouveau produit
                document.getElementById('code-barre-recupere').value = codeBarre;
                document.getElementById('formulaire-produit').style.display = 'block';
                document.getElementById('produit-info').innerHTML = '';
                document.getElementById('scan-status').innerHTML = 
                    '<p class="info">Nouveau produit. Veuillez compléter le formulaire.</p>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('scan-status').innerHTML = 
                '<p class="error">Erreur de communication avec le serveur</p>';
        });
}

function lancerScan() {
    demarrerScanner();
    document.getElementById('scan-preview').style.display = 'block';
    document.getElementById('btn-scan').style.display = 'none';
    document.getElementById('btn-arreter').style.display = 'inline-block';
}

function arreterScan() {
    arreterScanner();
    document.getElementById('scan-preview').style.display = 'none';
    document.getElementById('btn-scan').style.display = 'inline-block';
    document.getElementById('btn-arreter').style.display = 'none';
}