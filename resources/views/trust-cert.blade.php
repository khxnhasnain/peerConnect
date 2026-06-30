<x-app-layout>
    <div class="max-w-2xl mx-auto py-12 px-4">
        <div class="bg-white rounded-xl shadow p-8 space-y-6">
            <h1 class="text-2xl font-bold text-slate-900">Trust the local HTTPS certificate</h1>
            <p class="text-slate-600">
                Browsers show <strong>“Your connection is not private”</strong> until the mkcert root CA is installed on this device.
            </p>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900">
                Use meeting links on port <strong>8443</strong>, not 8001:<br>
                <code class="text-xs break-all">https://192.168.1.41:8443/meeting/YOUR_CODE</code>
            </div>

            <div>
                <h2 class="font-semibold text-lg mb-2">On this Mac</h2>
                <p class="text-slate-600 text-sm mb-2">Run once in Terminal (enter your Mac password when asked):</p>
                <pre class="bg-slate-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto">mkcert -install</pre>
                <p class="text-slate-500 text-sm mt-2">Then quit and reopen your browser, and reload this page.</p>
            </div>

            <div>
                <h2 class="font-semibold text-lg mb-2">On iPhone / iPad</h2>
                <ol class="list-decimal list-inside text-sm text-slate-600 space-y-1">
                    <li>Download the root CA on this device:</li>
                </ol>
                <a href="{{ route('dev.root-ca') }}"
                    class="inline-block mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    Download rootCA.pem
                </a>
                <ol class="list-decimal list-inside text-sm text-slate-600 space-y-1 mt-3" start="2">
                    <li>Open the file → Allow → install profile</li>
                    <li>Settings → General → About → Certificate Trust Settings</li>
                    <li>Enable full trust for the mkcert root</li>
                </ol>
            </div>

            <div>
                <h2 class="font-semibold text-lg mb-2">On Android</h2>
                <ol class="list-decimal list-inside text-sm text-slate-600 space-y-1">
                    <li><a href="{{ route('dev.root-ca') }}" class="text-blue-600 underline">Download rootCA.pem</a></li>
                    <li>Settings → Security → Encryption & credentials → Install a certificate → CA certificate</li>
                    <li>Select the downloaded file</li>
                </ol>
            </div>

            <div>
                <h2 class="font-semibold text-lg mb-2">Quick test only (not recommended)</h2>
                <p class="text-slate-600 text-sm">
                    Chrome on desktop: on the warning page click anywhere and type
                    <code class="bg-slate-100 px-1 rounded">thisisunsafe</code> (no text box appears).
                    Or click <strong>Advanced → Proceed to 192.168.1.41</strong>.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
