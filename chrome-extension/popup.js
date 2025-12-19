const serverSelect = document.getElementById("server");
const customServerInput = document.getElementById("customServer");
const endpointSelect = document.getElementById("endpoint");
const customEndpointInput = document.getElementById("customEndpoint");
const apiKeyInput = document.getElementById("apiKey");
const toggleApiKeyBtn = document.getElementById("toggleApiKey");
const saveBtn = document.getElementById("save");
const status = document.getElementById("status");

const SERVER_A = "https://server-a.com";
const SERVER_B = "https://server-b.com";

const COLORS = {
    error: "#dc2626",
    success: "#16a34a"
};

// ---------- helpers ----------
function isValidHost(value) {
    try {
        const url = new URL(value);
        // Allow http for local dev, https for production
        return ["http:", "https:"].includes(url.protocol);
    } catch {
        return false;
    }
}

function isValidEndpoint(value) {
    // If it's empty, it's invalid
    if (!value) return false;
    // Don't force a starting slash here, we handle it in the URL builder
    return !value.includes("://");
}

// ---------- load ----------
chrome.storage.sync.get(
    ["serverHost", "serverEndpoint", "apiKey"],
    ({ serverHost, serverEndpoint, apiKey }) => {

        if (serverHost) {
            if (serverHost === SERVER_A || serverHost === SERVER_B) {
                serverSelect.value = serverHost;
            } else {
                serverSelect.value = "custom";
                customServerInput.style.display = "block";
                customServerInput.value = serverHost;
            }
        }

        if (serverEndpoint) {
            if (serverEndpoint === "/webhooks/transcribe") {
                endpointSelect.value = serverEndpoint;
            } else {
                endpointSelect.value = "custom";
                customEndpointInput.style.display = "block";
                customEndpointInput.value = serverEndpoint;
            }
        }

        if (apiKey) {
            apiKeyInput.value = apiKey;
        }
    }
);

// ---------- UI ----------
serverSelect.addEventListener("change", () => {
    customServerInput.style.display =
        serverSelect.value === "custom" ? "block" : "none";
    status.textContent = "";
});

endpointSelect.addEventListener("change", () => {
    customEndpointInput.style.display =
        endpointSelect.value === "custom" ? "block" : "none";
    status.textContent = "";
});

// ---------- API KEY TOGGLE ----------
toggleApiKeyBtn.addEventListener("click", () => {
    const isHidden = apiKeyInput.type === "password";
    apiKeyInput.type = isHidden ? "text" : "password";
    toggleApiKeyBtn.querySelector("span").textContent = isHidden ? "🙈" : "👁️";
});

// ---------- save ----------
saveBtn.addEventListener("click", () => {
    let host = serverSelect.value === "custom"
        ? customServerInput.value.trim()
        : serverSelect.value;

    let endpoint = endpointSelect.value === "custom"
        ? customEndpointInput.value.trim()
        : endpointSelect.value;

    const apiKey = apiKeyInput.value.trim();

    // 1. Basic validation
    if (!host || !endpoint) {
        status.textContent = "Host and endpoint are required.";
        status.style.color = COLORS.error;
        return;
    }

    // 2. Host validation
    if (!isValidHost(host)) {
        status.textContent = "Invalid host (e.g., https://example.com)";
        status.style.color = COLORS.error;
        return;
    }

    // 3. Clean up the slash logic to prevent // errors
    host = host.replace(/\/$/, ""); // Remove trailing slash
    endpoint = endpoint.replace(/^\//, ""); // Remove leading slash

    chrome.storage.sync.set(
        { serverHost: host, serverEndpoint: endpoint, apiKey },
        () => {
            status.textContent = "Saved ✓";
            status.style.color = COLORS.success;
            // Force a log to the popup console to confirm
            console.log("Storage updated successfully");
        }
    );
});
