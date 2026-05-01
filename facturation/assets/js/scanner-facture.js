let codeReader = null;
let currentCodeBarre = '';

function lancerScan() {
    if (codeReader) codeReader.reset();
    codeReader = new ZXing.BrowserMultiFormatReader();

    codeReader.listVideoInputDevices()
        .then(devices => {
            if (devices.length === 0) {
                document.getElementById('scan-status').innerHTML = '<p style="color:red">Aucune caméra trouvée</p>';
                return;
            }
            const deviceId = devices[0].deviceId;
            codeReader.decodeFromVideoDevice(deviceId, 'video-preview', (result, err) => {
                if (result) {
                    traiterCodeBarreFacture(result.getText());
                    arreterScan();
                }
            });
            document.getElementById('scan-preview').style.display = 'block';
            document.getElementById('btn-scan').style.display = 'none';
            document.getElementById('btn-arreter').style.display = 'inline-block';
        })
        .catch(err => {
            document.getElementById('scan-status').innerHTML = '<p style="color:red">Erreur caméra</p>';
        });
}

function arreterScan() {
    if (codeReader) codeReader.reset();
    document.getElementById('scan-preview').style.display = 'none';
    document.getElementById('btn-scan').style.display = 'inline-block';
    document.getElementById('btn-arreter').style.display = 'none';
}

function traiterCodeBarreFacture(codeBarre) {
    fetch('/facturation/modules/produits/lire.php?code_barre=' + encodeURIComponent(codeBarre))
        .then(res => res.json())
        .then(data => {
            if (data.trouve) {
                currentCodeBarre = codeBarre;
                document.getElementById('code-barres-ajout').value = codeBarre;
                document.getElementById('ajout-quantite').style.display = 'block';
                document.getElementById('scan-status').innerHTML = '<p style="color:green">Produit : ' + data.produit.nom + ' (stock : ' + data.produit.quantite_stock + ')</p>';
            } else {
                document.getElementById('scan-status').innerHTML = '<p style="color:red">❌ Produit inconnu !</p>';
            }
        });
}

function ajouterAuPanier() {
    let qte = document.getElementById('quantite-ajout').value;
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    form.innerHTML = `
        <input type="hidden" name="code_barre" value="${currentCodeBarre}">
        <input type="hidden" name="quantite" value="${qte}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function annulerAjout() {
    document.getElementById('ajout-quantite').style.display = 'none';
    currentCodeBarre = '';
}