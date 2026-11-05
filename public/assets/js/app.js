document.addEventListener("DOMContentLoaded", () => {
  let turnstileLoader;
  let publicWidgetId;

  const loadTurnstile = () => {
    if (window.turnstile) return Promise.resolve();
    if (turnstileLoader) return turnstileLoader;

    turnstileLoader = new Promise((resolve, reject) => {
      const script = document.createElement("script");
      script.src =
        "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
      script.async = true;
      script.onload = () =>
        window.turnstile
          ? resolve()
          : reject(new Error("Turnstile unavailable"));
      script.onerror = () => reject(new Error("Turnstile failed to load"));
      document.head.append(script);
    });
    return turnstileLoader;
  };

  const mountPublicWidget = async (siteKey) => {
    const container = document.getElementById("public-turnstile");
    if (!container || !siteKey) return;
    container.hidden = false;
    try {
      await loadTurnstile();
      if (publicWidgetId !== undefined) return;
      publicWidgetId = window.turnstile.render(container, {
        sitekey: siteKey,
        action: container.dataset.action || "public_search",
        theme: "light",
        size: "flexible",
      });
    } catch {
      const message = document.getElementById("search-message");
      if (message)
        message.textContent =
          "Le contrôle anti-robot ne peut pas être chargé. Réessayez plus tard.";
    }
  };

  const resultsPanel = document.getElementById("search-result");
  const renderSearchResponse = (data) => {
    if (!resultsPanel) return;
    resultsPanel.replaceChildren();
    resultsPanel.hidden = !data.searched;
    const message = document.getElementById("search-message");
    if (message) {
      message.textContent = data.error || "";
      message.hidden = !data.error;
    }

    if (data.turnstileSiteKey) void mountPublicWidget(data.turnstileSiteKey);
    if (
      data.resetTurnstile &&
      publicWidgetId !== undefined &&
      window.turnstile
    ) {
      window.turnstile.reset(publicWidgetId);
    }
    if (data.turnstileUnavailable && message) {
      message.textContent =
        data.error || "Le contrôle anti-robot n’est pas configuré.";
    }
    if (!data.searched) return;

    resultsPanel.hidden = false;
    resultsPanel.className = "result-box mt-4";
    if (!Array.isArray(data.results) || data.results.length === 0) {
      const empty = document.createElement("p");
      empty.className = "mb-0";
      empty.textContent =
        "Aucune correspondance trouvée. Vérifiez l’orthographe, la date et l’année, ou rapprochez-vous du service de la scolarité.";
      resultsPanel.append(empty);
      return;
    }

    const labels = {
      DOSSIER_DEPOSE: "Dossier déposé",
      EN_COURS_ETABLISSEMENT: "Diplôme en cours d’établissement",
      SIGNE: "Diplôme signé",
      PRET_A_REMETTRE: "Diplôme disponible – prêt à être remis",
      REMIS: "Diplôme remis",
    };
    const heading = document.createElement("h3");
    heading.className = "h5";
    heading.textContent = "Résultat de la consultation";
    resultsPanel.append(heading);
    for (const result of data.results) {
      const row = document.createElement("p");
      row.className = "mb-1";
      const title = document.createElement("strong");
      title.textContent = `${result.type_diplome} · ${result.annee_obtention}`;
      row.append(title, document.createElement("br"));
      row.append(
        document.createTextNode(
          `Statut : ${labels[result.statut] || "Information à confirmer auprès de la scolarité"}`,
        ),
      );
      resultsPanel.append(row);
    }
    const note = document.createElement("p");
    note.className = "small text-muted mb-0 mt-2";
    note.textContent =
      "La remise nécessite la vérification des pièces au guichet.";
    resultsPanel.append(note);
  };

  const searchForm = document.getElementById("public-search-form");
  if (searchForm) {
    const initialSiteKey =
      document.getElementById("public-turnstile")?.dataset.sitekey;
    if (initialSiteKey) void mountPublicWidget(initialSiteKey);

    searchForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const submit = searchForm.querySelector('button[type="submit"]');
      const originalLabel = submit?.textContent;
      if (submit) {
        submit.disabled = true;
        submit.textContent = "Vérification…";
      }
      const message = document.getElementById("search-message");
      if (message) message.textContent = "";
      if (message) message.hidden = true;
      const formData = new FormData(searchForm);
      const includedTurnstileToken = formData.has("cf-turnstile-response");

      try {
        const response = await fetch(searchForm.action, {
          method: "POST",
          body: formData,
          headers: { Accept: "application/json" },
          credentials: "same-origin",
        });
        const data = await response.json();
        renderSearchResponse(data);
        if (
          includedTurnstileToken &&
          !data.resetTurnstile &&
          publicWidgetId !== undefined &&
          window.turnstile
        ) {
          window.turnstile.reset(publicWidgetId);
        }
      } catch {
        if (message) {
          message.textContent =
            "La vérification n’a pas abouti. Vérifiez votre connexion et réessayez.";
          message.hidden = false;
        }
        if (publicWidgetId !== undefined && window.turnstile) {
          window.turnstile.reset(publicWidgetId);
        }
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.textContent = originalLabel || "Vérifier";
        }
      }
    });
  }

  document.querySelectorAll("[data-toggle-password]").forEach((button) => {
    const input = document.getElementById(button.dataset.togglePassword);
    if (!input) return;

    button.addEventListener("click", () => {
      const visible = input.type === "password";
      input.type = visible ? "text" : "password";
      button.setAttribute("aria-pressed", String(visible));
      button.setAttribute(
        "aria-label",
        visible ? "Masquer le mot de passe" : "Afficher le mot de passe",
      );
    });
  });

  document.querySelectorAll("form").forEach((form) =>
    form.addEventListener("submit", () => {
      if (form.matches("[data-async-form]")) return;
      const button = form.querySelector(
        "button[type=submit], button:not([type])",
      );
      if (button) {
        button.dataset.original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = "Traitement…";
      }
    }),
  );
});
