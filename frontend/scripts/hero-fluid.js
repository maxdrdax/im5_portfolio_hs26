// =====================================================================
// -> Hero: flüssige Verzerrung unter der Maus (WebGL)
// Fährt die Maus über den Hero, verzieht sich das Bild bzw. Video leicht wie Wasser und beruhigt
// sich wieder. Am Rand der Verzerrung verschieben sich Rot und Blau leicht (Chroma).
//
// So funktioniert es:
// 1. «Feld»: Ein kleines, unsichtbares Bild (Textur) speichert pro Stelle, in welche Richtung und wie
//    stark verschoben wird. Jedes Bild (ca. 60× pro Sekunde) wird die Mausbewegung hineingemalt,
//    leicht mitgetragen (wirkt flüssig) und abgeschwächt (beruhigt sich).
// 2. «Ausgabe»: Ein Shader (kleines Programm auf der Grafikkarte) zeichnet das Hero-Bild bzw. das
//    aktuelle Videobild und liest jede Stelle um den Wert aus dem Feld verschoben aus.
//
// Nur mit Maus und ohne «Bewegung reduzieren». Ohne WebGL bleibt der Hero, wie er ist.
// Das <canvas> ist nur sichtbar und rechnet nur, solange sich etwas bewegt; danach sieht man
// wieder das normale <img>/<video> (gleiches Bild, darum ist der Wechsel unsichtbar).
// =====================================================================

// ▼ Zum Ausprobieren anpassen
const FLUID_STRENGTH = 0.035; // wie weit das Bild verschoben wird (Anteil der Bildbreite)
const FLUID_CHROMA = 0.35; // Farbverschiebung am Rand (0 = keine)
const FLUID_RADIUS = 0.12; // Grösse des Bereichs um den Cursor (Anteil der Bildhöhe)
const FLUID_FORCE = 10; // wie stark die Mausbewegung wirkt
const FLUID_DECAY = 0.94; // wie schnell es sich beruhigt (näher bei 1 = langsamer)
const FLUID_IDLE = 1500; // ms ohne Bewegung, bis der Effekt pausiert
// ▲

const FIELD_SIZE = 128; // Breite des Feldes in Pixeln (grob reicht, wird weich interpoliert)
const MAX_PIXEL_RATIO = 1.5; // schont die Grafikkarte auf Retina-Bildschirmen

const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Gemeinsamer Vertex-Shader: ein Rechteck über die ganze Fläche, vUv = Position 0–1
const VERTEX = `
attribute vec2 aPos;
varying vec2 vUv;
void main() {
    vUv = aPos * 0.5 + 0.5;
    gl_Position = vec4(aPos, 0.0, 1.0);
}`;

// Feld: Verschiebung als Farbe gespeichert (0.5 = keine Verschiebung, 0 bis 1 = -1 bis +1)
const FIELD_SHADER = `
precision highp float;
uniform sampler2D uField;
uniform vec2 uMouse;
uniform vec2 uForce;
uniform float uAspect;
uniform float uRadius;
uniform float uDecay;
varying vec2 vUv;

vec2 readField(vec2 uv) {
    return texture2D(uField, uv).xy * 2.0 - 1.0;
}

void main() {
    // Mittragen: Wert von der Stelle holen, von der die Bewegung herkommt (wirkt flüssig)
    vec2 v = readField(vUv);
    v = readField(vUv - v * 0.01);
    // Abschwächen; kleinste Reste auf 0 setzen, damit das Bild wirklich zur Ruhe kommt
    v *= uDecay;
    v = sign(v) * max(abs(v) - 0.006, 0.0);
    // Mausbewegung hineinmalen: weicher Kreis um den Cursor (Gauss-Kurve)
    vec2 d = vUv - uMouse;
    d.x *= uAspect;
    v += uForce * exp(-dot(d, d) / (uRadius * uRadius));
    v = clamp(v, -1.0, 1.0);
    gl_FragColor = vec4(v * 0.5 + 0.5, 0.0, 1.0);
}`;

// Ausgabe: Bild/Video wie object-fit: cover, verschoben um den Wert aus dem Feld
const RENDER_SHADER = `
precision highp float;
uniform sampler2D uMedia;
uniform sampler2D uField;
uniform vec2 uScale;
uniform float uStrength;
uniform float uChroma;
varying vec2 vUv;

void main() {
    vec2 v = texture2D(uField, vUv).xy * 2.0 - 1.0;
    vec2 uv = (vUv - 0.5) * uScale + 0.5;
    vec2 offset = v * uStrength;
    // Rot, Grün und Blau leicht unterschiedlich weit verschoben = Farbsaum (Chroma)
    float r = texture2D(uMedia, uv - offset * (1.0 + uChroma)).r;
    float g = texture2D(uMedia, uv - offset).g;
    float b = texture2D(uMedia, uv - offset * (1.0 - uChroma)).b;
    gl_FragColor = vec4(r, g, b, 1.0);
}`;

function createProgram(gl, fragmentSource) {
    const compile = (type, source) => {
        const shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            throw new Error(gl.getShaderInfoLog(shader));
        }
        return shader;
    };
    const program = gl.createProgram();
    gl.attachShader(program, compile(gl.VERTEX_SHADER, VERTEX));
    gl.attachShader(program, compile(gl.FRAGMENT_SHADER, fragmentSource));
    gl.bindAttribLocation(program, 0, 'aPos');
    gl.linkProgram(program);
    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        throw new Error(gl.getProgramInfoLog(program));
    }
    // Alle uniform-Adressen einmal nachschlagen
    const uniforms = {};
    const count = gl.getProgramParameter(program, gl.ACTIVE_UNIFORMS);
    for (let i = 0; i < count; i++) {
        const name = gl.getActiveUniform(program, i).name;
        uniforms[name] = gl.getUniformLocation(program, name);
    }
    return { program, uniforms };
}

// Textur mit Einstellungen, die für beliebige Bildgrössen funktionieren (WebGL 1)
function createTexture(gl) {
    const texture = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, texture);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    return texture;
}

// Feld-Textur, die man auch als Zeichenfläche (Framebuffer) verwenden kann; Start = 128 (keine Verschiebung)
function createFieldTarget(gl, width, height) {
    const texture = createTexture(gl);
    const data = new Uint8Array(width * height * 4).fill(128);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, width, height, 0, gl.RGBA, gl.UNSIGNED_BYTE, data);
    const framebuffer = gl.createFramebuffer();
    gl.bindFramebuffer(gl.FRAMEBUFFER, framebuffer);
    gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, texture, 0);
    gl.bindFramebuffer(gl.FRAMEBUFFER, null);
    return { texture, framebuffer };
}

function initHeroFluid(hero) {
    const image = hero.querySelector('img.image');
    const video = hero.querySelector('video.image');
    if (!image) {
        return;
    }

    const canvas = document.createElement('canvas');
    canvas.className = 'hero-fluid';
    canvas.setAttribute('aria-hidden', 'true');
    const gl = canvas.getContext('webgl', { alpha: false, antialias: false, depth: false });
    if (!gl) {
        return; // kein WebGL: Hero bleibt unverändert
    }

    let fieldProgram;
    let renderProgram;
    try {
        fieldProgram = createProgram(gl, FIELD_SHADER);
        renderProgram = createProgram(gl, RENDER_SHADER);
    } catch (error) {
        return;
    }

    // Rechteck aus zwei Dreiecken über die ganze Fläche
    gl.bindBuffer(gl.ARRAY_BUFFER, gl.createBuffer());
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
    gl.enableVertexAttribArray(0);
    gl.vertexAttribPointer(0, 2, gl.FLOAT, false, 0, 0);
    // Bilder haben y nach unten, WebGL nach oben
    gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);

    const mediaTexture = createTexture(gl);
    let fieldWidth = 0;
    let fieldHeight = 0;
    let fields = []; // zwei Felder im Wechsel: aus dem einen lesen, ins andere schreiben («Ping-Pong»)
    let mediaSource = null; // was gerade in mediaTexture steckt (Bild oder Video)
    let mediaWidth = 1;
    let mediaHeight = 1;

    // Video verwenden, sobald es läuft (sonst liegt das Bild sichtbar oben)
    const currentSource = () => (video && video.classList.contains('is-playing') && video.readyState >= 2 ? video : image);

    // Animiertes Bild (WebP/GIF, data-animated aus PHP): wie ein Video jedes Bild neu hochladen
    const animatedImage = image.hasAttribute('data-animated');

    const uploadMedia = () => {
        const source = currentSource();
        // Standbild nur einmal hochladen, Video und animiertes Bild jedes Bild neu (sie laufen ja)
        if (source === mediaSource && source === image && !animatedImage) {
            return true;
        }
        if (source === image && !(image.complete && image.naturalWidth)) {
            return false;
        }
        gl.bindTexture(gl.TEXTURE_2D, mediaTexture);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, source);
        mediaSource = source;
        mediaWidth = source === video ? video.videoWidth : image.naturalWidth;
        mediaHeight = source === video ? video.videoHeight : image.naturalHeight;
        return true;
    };

    // Grösse an den Hero anpassen (beim Start jeder Bewegung, falls das Fenster verändert wurde)
    const resize = () => {
        const ratio = Math.min(window.devicePixelRatio || 1, MAX_PIXEL_RATIO);
        const width = Math.round(hero.clientWidth * ratio);
        const height = Math.round(hero.clientHeight * ratio);
        if (canvas.width !== width || canvas.height !== height) {
            canvas.width = width;
            canvas.height = height;
        }
        const newFieldHeight = Math.max(1, Math.round(FIELD_SIZE * hero.clientHeight / hero.clientWidth));
        if (fieldWidth !== FIELD_SIZE || fieldHeight !== newFieldHeight) {
            fieldWidth = FIELD_SIZE;
            fieldHeight = newFieldHeight;
            fields = [createFieldTarget(gl, fieldWidth, fieldHeight), createFieldTarget(gl, fieldWidth, fieldHeight)];
        }
    };

    // Maus in Feld-Koordinaten (0–1, y nach oben)
    const mouse = { x: 0.5, y: 0.5 };
    const lastMouse = { x: 0.5, y: 0.5 };
    let lastMove = 0;
    let frame = null;

    const draw = () => {
        // Kein Bild (nur Video ohne Poster), Fehler beim Hochladen (z. B. Bild von fremder Domain): aufhören
        try {
            if (!uploadMedia()) {
                frame = requestAnimationFrame(draw);
                return;
            }
        } catch (error) {
            stop();
            return;
        }

        const aspect = hero.clientWidth / hero.clientHeight;

        // 1. Feld weiterrechnen
        const [read, write] = fields;
        gl.bindFramebuffer(gl.FRAMEBUFFER, write.framebuffer);
        gl.viewport(0, 0, fieldWidth, fieldHeight);
        gl.useProgram(fieldProgram.program);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, read.texture);
        gl.uniform1i(fieldProgram.uniforms.uField, 0);
        gl.uniform2f(fieldProgram.uniforms.uMouse, mouse.x, mouse.y);
        gl.uniform2f(fieldProgram.uniforms.uForce, (mouse.x - lastMouse.x) * FLUID_FORCE, (mouse.y - lastMouse.y) * FLUID_FORCE);
        gl.uniform1f(fieldProgram.uniforms.uAspect, aspect);
        gl.uniform1f(fieldProgram.uniforms.uRadius, FLUID_RADIUS);
        gl.uniform1f(fieldProgram.uniforms.uDecay, FLUID_DECAY);
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        fields = [write, read];
        lastMouse.x = mouse.x;
        lastMouse.y = mouse.y;

        // 2. Bild mit Verschiebung zeichnen
        gl.bindFramebuffer(gl.FRAMEBUFFER, null);
        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.useProgram(renderProgram.program);
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, mediaTexture);
        gl.uniform1i(renderProgram.uniforms.uMedia, 0);
        gl.activeTexture(gl.TEXTURE1);
        gl.bindTexture(gl.TEXTURE_2D, write.texture);
        gl.uniform1i(renderProgram.uniforms.uField, 1);
        // object-fit: cover nachbauen: Die längere Seite wird abgeschnitten, Mitte bleibt Mitte
        const mediaAspect = mediaWidth / mediaHeight;
        gl.uniform2f(
            renderProgram.uniforms.uScale,
            aspect > mediaAspect ? 1 : aspect / mediaAspect,
            aspect > mediaAspect ? mediaAspect / aspect : 1
        );
        gl.uniform1f(renderProgram.uniforms.uStrength, FLUID_STRENGTH);
        gl.uniform1f(renderProgram.uniforms.uChroma, FLUID_CHROMA);
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

        // Erst nach dem ersten fertigen Bild einblenden (kein schwarzes Aufblitzen)
        canvas.classList.add('is-active');

        // Lange keine Bewegung: Feld ist zur Ruhe gekommen → pausieren, normales Bild zeigen
        if (performance.now() - lastMove > FLUID_IDLE) {
            stop();
            return;
        }
        frame = requestAnimationFrame(draw);
    };

    const stop = () => {
        cancelAnimationFrame(frame);
        frame = null;
        canvas.classList.remove('is-active');
    };

    const toFieldPosition = (event) => {
        const rect = hero.getBoundingClientRect();
        return {
            x: (event.clientX - rect.left) / rect.width,
            y: 1 - (event.clientY - rect.top) / rect.height,
        };
    };

    hero.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse') {
            return;
        }
        const position = toFieldPosition(event);
        mouse.x = position.x;
        mouse.y = position.y;
        lastMove = performance.now();
        if (frame === null) {
            // Neu starten: ohne Sprung (sonst wirkt die Strecke seit dem letzten Mal wie ein Ruck)
            lastMouse.x = mouse.x;
            lastMouse.y = mouse.y;
            resize();
            frame = requestAnimationFrame(draw);
        }
    });

    // Grafikkarte hat den Kontext entzogen (z. B. zu viele Tabs): Effekt ausschalten
    canvas.addEventListener('webglcontextlost', () => {
        stop();
        canvas.remove();
    });

    // Über Bild und Video, aber unter Titel, Pfeil und dem Verlauf oben (::after)
    (video || image).after(canvas);
}

if (canHover && !reducedMotion) {
    document.querySelectorAll('[data-module="hero-image"], [data-page="project"] .project-hero').forEach(initHeroFluid);
}
