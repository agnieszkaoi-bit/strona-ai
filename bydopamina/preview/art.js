/*
 * Podgląd: rysuje zastępcze „packshoty” biżuterii w SVG w elementach [data-art].
 * Tylko do prezentacji układu – w sklepie są tu prawdziwe zdjęcia.
 */
(() => {
	const gold = (id) => `
		<linearGradient id="g${id}" x1="0" y1="0" x2="1" y2="1">
			<stop offset="0" stop-color="#F7E3A8"/><stop offset=".35" stop-color="#D4AF62"/>
			<stop offset=".6" stop-color="#9C7a3C"/><stop offset=".85" stop-color="#E9CD86"/><stop offset="1" stop-color="#B38B45"/>
		</linearGradient>`;
	const skin = (id) => `
		<linearGradient id="s${id}" x1="0" y1="0" x2="0" y2="1">
			<stop offset="0" stop-color="#E7C3A6"/><stop offset="1" stop-color="#C99877"/>
		</linearGradient>`;
	const chain = (d, id, w = 2.2) => `<path d="${d}" fill="none" stroke="url(#g${id})" stroke-width="${w}" stroke-linecap="round" stroke-dasharray="3.2 1.6"/>`;

	const scenes = {
		necklace: (id) => `${chain('M70 40 C 90 190, 210 190, 230 40', id)}
			<circle cx="150" cy="176" r="12" fill="none" stroke="url(#g${id})" stroke-width="5"/>
			<ellipse cx="150" cy="248" rx="70" ry="8" fill="#000" opacity=".06"/>`,
		layered: (id) => `${chain('M60 30 C 80 150, 220 150, 240 30', id, 1.8)}
			${chain('M60 30 C 75 205, 225 205, 240 30', id, 2.2)}
			<path d="M150 200 l-9 18 9 16 9-16z" fill="url(#g${id})"/>
			${chain('M60 30 C 70 250, 230 250, 240 30', id, 1.6)}`,
		hoops: (id) => `<circle cx="112" cy="140" r="46" fill="none" stroke="url(#g${id})" stroke-width="11"/>
			<circle cx="196" cy="150" r="46" fill="none" stroke="url(#g${id})" stroke-width="11"/>
			<ellipse cx="154" cy="236" rx="90" ry="8" fill="#000" opacity=".06"/>`,
		ring: (id) => `<ellipse cx="150" cy="160" rx="62" ry="58" fill="none" stroke="url(#g${id})" stroke-width="12"/>
			<path d="M150 88 l-16 10 16 16 16-16z" fill="#F4EFE6" stroke="url(#g${id})" stroke-width="3"/>
			<ellipse cx="150" cy="236" rx="70" ry="8" fill="#000" opacity=".07"/>`,
		bracelet: (id) => `<ellipse cx="150" cy="150" rx="92" ry="46" fill="none" stroke="url(#g${id})" stroke-width="9" stroke-dasharray="14 3"/>
			<circle cx="150" cy="196" r="7" fill="url(#g${id})"/>
			<ellipse cx="150" cy="232" rx="96" ry="8" fill="#000" opacity=".06"/>`,
		studs: (id) => `<circle cx="120" cy="140" r="16" fill="url(#g${id})"/><circle cx="185" cy="140" r="16" fill="url(#g${id})"/>
			<circle cx="120" cy="140" r="6" fill="#fff" opacity=".45"/><circle cx="185" cy="140" r="6" fill="#fff" opacity=".45"/>`,
		model: (id) => `<path d="M95 0 C 100 70, 90 120, 20 170 C -10 190, -20 300, -20 300 L 320 300 C 320 300, 310 190, 280 170 C 210 120, 200 70, 205 0 Z" fill="url(#s${id})"/>
			<path d="M20 170 C 70 150, 110 175, 150 178 C 190 175, 230 150, 280 170" fill="none" stroke="#B88767" stroke-width="1.2" opacity=".5"/>
			${chain('M104 60 C 112 150, 188 150, 196 60', id, 2)}
			${chain('M100 60 C 105 205, 195 205, 200 60', id, 2.4)}
			<circle cx="150" cy="190" r="9" fill="none" stroke="url(#g${id})" stroke-width="4"/>
			${chain('M96 60 C 96 262, 204 262, 204 60', id, 1.6)}`,
		hand: (id) => `<path d="M60 300 C 70 230, 90 200, 118 150 L 128 40 C 130 26, 150 26, 150 40 L 152 120 L 158 30 C 160 16, 180 16, 180 30 L 180 125 L 190 50 C 192 36, 210 38, 210 52 L 206 140 L 216 90 C 219 78, 236 80, 234 94 L 226 200 C 222 250, 200 280, 200 300 Z" fill="url(#s${id})"/>
			<rect x="151" y="98" width="30" height="8" rx="3" fill="url(#g${id})" transform="rotate(3 166 102)"/>
			<rect x="182" y="112" width="26" height="7" rx="3" fill="url(#g${id})"/>`,
		box: (id) => `<rect x="60" y="110" width="180" height="120" fill="#1C1917"/><rect x="50" y="92" width="200" height="30" fill="#2A2522"/>
			<rect x="142" y="92" width="16" height="138" fill="#9C1C33"/>
			<path d="M150 92 C 120 60, 96 72, 120 92 M150 92 C 180 60, 204 72, 180 92" fill="none" stroke="#9C1C33" stroke-width="8"/>`,
		water: (id) => `<rect width="300" height="300" fill="#C9D3D1"/><path d="M0 120 C 60 100, 90 140, 150 120 S 240 100, 300 124 L 300 300 L 0 300 Z" fill="#AEBFBD"/>
			${chain('M60 40 C 80 220, 220 220, 240 40', id, 2.6)}
			<path d="M0 180 C 50 170, 100 196, 160 180 S 250 168, 300 186" fill="none" stroke="#fff" stroke-width="1.4" opacity=".6"/>`,
	};

	let n = 0;
	document.querySelectorAll('[data-art]').forEach((el) => {
		const id = ++n;
		const kind = el.dataset.art;
		const bg = el.dataset.bg || '#EDE6DC';
		const ratio = el.dataset.ratio || '4 / 5';
		el.classList.add('bd-ph');
		el.style.cssText += `aspect-ratio:${ratio};background:${bg};display:block;width:100%;overflow:hidden`;
		const vb = kind === 'model' || kind === 'hand' || kind === 'water' ? '0 0 300 300' : '0 0 300 300';
		el.innerHTML = `<svg viewBox="${vb}" preserveAspectRatio="xMidYMid slice" width="100%" height="100%" aria-hidden="true"><defs>${gold(id)}${skin(id)}</defs>${scenes[kind](id)}</svg>`;
	});
})();
