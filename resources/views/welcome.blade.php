<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyNaga | Baha Predictor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(16, 185, 129, 0.1) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(16, 185, 129, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-emerald-500 selection:text-white">

    <nav class="fixed w-full z-50 top-0 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-xl font-black tracking-tight text-gray-900">MyNaga <span class="text-emerald-600 font-semibold">Baha Predictor</span></span>
            </div>
            <div>
                <a href="/map" class="px-5 py-2.5 bg-gray-900 hover:bg-emerald-600 text-white text-sm font-bold rounded-full transition-all shadow-md active:scale-95">
                    Open Live Map
                </a>
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 bg-grid-pattern pointer-events-none [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold uppercase tracking-widest mb-6">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                BU MADYA Hackathon Pitch
            </div>

            <h1 class="text-5xl lg:text-7xl font-black tracking-tight text-gray-900 mb-6 leading-tight">
                Predict. Prepare. <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-400">Protect Naga City.</span>
            </h1>

            <p class="mt-4 text-lg lg:text-xl text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                An intelligent disaster resilience hub integrating real-time telemetry, topographic data, and machine learning to forecast localized flood risks before they happen.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/map" class="w-full sm:w-auto px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-full transition-all shadow-lg shadow-emerald-500/30 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    Explore Flood Map
                </a>
            </div>
        </div>
    </section>


    <section class="max-w-5xl mx-auto px-6 -mt-10 mb-24 relative z-20">
        <div class="rounded-3xl shadow-2xl overflow-hidden border border-gray-100 bg-white p-2">
            <div class="aspect-video bg-gray-100 rounded-2xl overflow-hidden relative flex items-center justify-center">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 to-gray-900 opacity-90"></div>
                <div class="text-center z-10 text-white">
                    <svg class="w-16 h-16 mx-auto mb-4 text-emerald-400 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                    <p class="font-bold tracking-widest uppercase text-sm opacity-80">Interactive Dashboard Preview</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-24 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black text-gray-900">Next-Generation Resilience</h2>
                <p class="mt-4 text-gray-500">How the Baha Predictor transforms data into safety.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-gray-50 border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all group">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white text-emerald-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">AI Water Level Prediction</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Utilizing the custom TURO-MOKO machine learning model to calculate localized flood heights based on real-time rainfall, historic data, and area elevation.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-gray-50 border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all group">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white text-emerald-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Live Risk Mapping</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        An interactive, localized map of Naga City that instantly identifies hotspots. Zones are dynamically categorized as Clear, Moderate, or Flooded.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-gray-50 border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all group">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white text-emerald-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Environmental Profiling</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Analyzes the unique topography of each street, factoring in Above Sea Level (ASL) elevation and local drainage infrastructure quality for pin-point accuracy.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-gray-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-emerald-900/20"></div>
        <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
            <h2 class="text-3xl lg:text-5xl font-black mb-6">Designed for Naga. Built for the Future.</h2>
            <p class="text-gray-400 text-lg mb-10 leading-relaxed">
                By integrating this predictor into the MyNaga app ecosystem, we empower citizens with actionable intelligence. It's not just about knowing it will rain; it's about knowing exactly which streets are safe to cross.
            </p>
            <a href="/map" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-gray-900 font-bold rounded-full hover:bg-emerald-50 transition-colors">
                Start the Simulation Demo
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </section>

    <footer class="bg-white border-t border-gray-100 py-10 text-center">
        <p class="text-sm text-gray-400 font-medium">
            Developed for the BU MADYA Hackathon • Naga City, Bicol
        </p>
    </footer>

</body>
</html>
