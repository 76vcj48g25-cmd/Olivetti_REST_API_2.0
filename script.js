const API_BASE = "api.php";

const formGiocatore = document.getElementById("formGiocatore");
const listaGiocatori = document.getElementById("listaGiocatori");
const selectCategoria = document.getElementById("categoria");
const filtroCategoria = document.getElementById("filtroCategoria");
const messaggio = document.getElementById("messaggio");

let giocatoriCorrenti = [];

document.addEventListener("DOMContentLoaded", () => {
    caricaCategorie();
    caricaGiocatori();
});

async function caricaCategorie() {
    try {
        const risposta = await fetch(`${API_BASE}/categorie`);
        const dati = await risposta.json();

        if (dati.successo) {
            dati.categorie.forEach(categoria => {
                const optionForm = document.createElement("option");
                optionForm.value = categoria;
                optionForm.textContent = categoria;
                selectCategoria.appendChild(optionForm);

                const optionFiltro = document.createElement("option");
                optionFiltro.value = categoria;
                optionFiltro.textContent = categoria;
                filtroCategoria.appendChild(optionFiltro);
            });
        }
    } catch (errore) {
        mostraMessaggio("Errore nel caricamento delle categorie", "errore");
    }
}

async function caricaGiocatori() {
    try {
        const risposta = await fetch(`${API_BASE}/tutti`);
        const dati = await risposta.json();

        if (dati.successo) {
            giocatoriCorrenti = dati.giocatori;
            mostraGiocatori(giocatoriCorrenti);
        }
    } catch (errore) {
        mostraMessaggio("Errore nel caricamento dei giocatori", "errore");
    }
}

function mostraGiocatori(giocatori) {
    listaGiocatori.innerHTML = "";

    if (giocatori.length === 0) {
        listaGiocatori.innerHTML = "<p>Nessun giocatore trovato.</p>";
        return;
    }

    giocatori.forEach(giocatore => {
        const card = document.createElement("div");
        card.classList.add("card-giocatore");

        card.innerHTML = `
            <h3>${giocatore.nome} ${giocatore.cognome}</h3>
            <p><strong>Età:</strong> ${giocatore.eta}</p>
            <p><strong>Categoria:</strong> ${giocatore.categoria}</p>
        `;

        listaGiocatori.appendChild(card);
    });
}

formGiocatore.addEventListener("submit", async (event) => {
    event.preventDefault();

    const nuovoGiocatore = {
        nome: document.getElementById("nome").value,
        cognome: document.getElementById("cognome").value,
        eta: document.getElementById("eta").value,
        categoria: document.getElementById("categoria").value
    };

    try {
        const risposta = await fetch(`${API_BASE}/nuovo`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(nuovoGiocatore)
        });

        const dati = await risposta.json();

        if (!risposta.ok) {
            mostraMessaggio(dati.messaggio, "errore");
            return;
        }

        giocatoriCorrenti.push(dati.giocatore);
        mostraGiocatori(giocatoriCorrenti);

        formGiocatore.reset();
        mostraMessaggio(dati.messaggio, "successo");

    } catch (errore) {
        mostraMessaggio("Errore durante l'inserimento del giocatore", "errore");
    }
});

filtroCategoria.addEventListener("change", async () => {
    const categoriaScelta = filtroCategoria.value;

    if (categoriaScelta === "tutti") {
        caricaGiocatori();
        return;
    }

    try {
        const risposta = await fetch(`${API_BASE}/categoria/${encodeURIComponent(categoriaScelta)}`);
        const dati = await risposta.json();

        if (dati.successo) {
            mostraGiocatori(dati.giocatori);
        }
    } catch (errore) {
        mostraMessaggio("Errore durante il filtro per categoria", "errore");
    }
});

function mostraMessaggio(testo, tipo) {
    messaggio.textContent = testo;
    messaggio.className = tipo;
}