/**
 * Client-Side Multi-Layer Second-Phone Screen & Spoofing Detector + 3D Liveness Helpers
 * Designed for GoMSN Attendance Biometric Verification (Pure browser Canvas + face-api landmarks).
 */
(function (global) {
    'use strict';

    const WORK_W = 240;
    const WORK_H = 180;
    const CROP_MAX = 140;

    const CHALLENGES = [
        { type: 'blink', label: 'Kedipkan mata Anda 1 kali', icon: '👀' },
        { type: 'turn_left', label: 'Tengok sedikit ke kiri, lalu hadap depan', icon: '⬅️' },
        { type: 'turn_right', label: 'Tengok sedikit ke kanan, lalu hadap depan', icon: '➡️' },
    ];

    function clamp(val, min, max) {
        return Math.max(min, Math.min(max, val));
    }

    function luma(r, g, b) {
        return 0.299 * r + 0.587 * g + 0.114 * b;
    }

    function getLumaAt(data, w, x, y) {
        const idx = (y * w + x) * 4;
        return 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
    }

    /**
     * Detect straight rectangular bezel / screen edges around the face viewport.
     */
    function computeBezelScore(data, fx, fy, fw, fh) {
        const spanYStart = clamp(Math.floor(fy - fh * 0.35), 4, WORK_H - 6);
        const spanYEnd = clamp(Math.ceil(fy + fh * 1.35), spanYStart + 8, WORK_H - 4);
        const spanXStart = clamp(Math.floor(fx - fw * 0.35), 4, WORK_W - 6);
        const spanXEnd = clamp(Math.ceil(fx + fw * 1.35), spanXStart + 8, WORK_W - 4);

        function scanVerticalRange(xStart, xEnd, innerDirection) {
            let bestContinuity = 0;
            let bestEmissive = false;
            const totalRows = Math.max(1, spanYEnd - spanYStart);

            for (let x = xStart; x <= xEnd; x++) {
                let edgeHits = 0;
                let polarityHits = 0;
                let dropSum = 0;

                for (let y = spanYStart; y < spanYEnd; y++) {
                    const lInner = getLumaAt(data, WORK_W, clamp(x + innerDirection * 2, 0, WORK_W - 1), y);
                    const lOuter = getLumaAt(data, WORK_W, clamp(x - innerDirection * 2, 0, WORK_W - 1), y);
                    const diff = lInner - lOuter;
                    if (Math.abs(diff) >= 24) {
                        edgeHits++;
                        if (diff >= 14) {
                            polarityHits++;
                            dropSum += diff;
                        }
                    }
                }

                const continuity = edgeHits / totalRows;
                const polarityRatio = edgeHits > 0 ? polarityHits / edgeHits : 0;
                const meanDrop = polarityHits > 0 ? dropSum / polarityHits : 0;
                const isEmissive = continuity >= 0.52 && polarityRatio >= 0.58 && meanDrop >= 16;

                if (continuity > bestContinuity) {
                    bestContinuity = continuity;
                }
                if (isEmissive) {
                    bestEmissive = true;
                }
            }

            return { continuity: bestContinuity, emissive: bestEmissive };
        }

        function scanHorizontalRange(yStart, yEnd, innerDirection) {
            let bestContinuity = 0;
            let bestEmissive = false;
            const totalCols = Math.max(1, spanXEnd - spanXStart);

            for (let y = yStart; y <= yEnd; y++) {
                let edgeHits = 0;
                let polarityHits = 0;
                let dropSum = 0;

                for (let x = spanXStart; x < spanXEnd; x++) {
                    const lInner = getLumaAt(data, WORK_W, x, clamp(y + innerDirection * 2, 0, WORK_H - 1));
                    const lOuter = getLumaAt(data, WORK_W, x, clamp(y - innerDirection * 2, 0, WORK_H - 1));
                    const diff = lInner - lOuter;
                    if (Math.abs(diff) >= 24) {
                        edgeHits++;
                        if (diff >= 14) {
                            polarityHits++;
                            dropSum += diff;
                        }
                    }
                }

                const continuity = edgeHits / totalCols;
                const polarityRatio = edgeHits > 0 ? polarityHits / edgeHits : 0;
                const meanDrop = polarityHits > 0 ? dropSum / polarityHits : 0;
                const isEmissive = continuity >= 0.52 && polarityRatio >= 0.58 && meanDrop >= 16;

                if (continuity > bestContinuity) {
                    bestContinuity = continuity;
                }
                if (isEmissive) {
                    bestEmissive = true;
                }
            }

            return { continuity: bestContinuity, emissive: bestEmissive };
        }

        const left = scanVerticalRange(4, Math.max(6, Math.floor(fx)), 1);
        const right = scanVerticalRange(Math.min(WORK_W - 6, Math.ceil(fx + fw)), WORK_W - 4, -1);
        const top = scanHorizontalRange(4, Math.max(6, Math.floor(fy)), 1);
        const bottom = scanHorizontalRange(Math.min(WORK_H - 6, Math.ceil(fy + fh)), WORK_H - 4, -1);

        const sides = [left, right, top, bottom];
        let strongSides = 0;
        let emissiveSides = 0;
        let maxContinuity = 0;

        for (let i = 0; i < sides.length; i++) {
            if (sides[i].continuity >= 0.56) strongSides++;
            if (sides[i].emissive) emissiveSides++;
            if (sides[i].continuity > maxContinuity) maxContinuity = sides[i].continuity;
        }

        const oppositeSides = (left.continuity >= 0.56 && right.continuity >= 0.56) ||
                              (top.continuity >= 0.56 && bottom.continuity >= 0.56);

        if (strongSides >= 3) {
            return emissiveSides >= 2 ? 42 : 40;
        }
        if (strongSides === 2 && (oppositeSides || emissiveSides >= 1)) {
            return 28;
        }
        if (strongSides === 2) {
            return 20;
        }
        if (emissiveSides >= 1 && maxContinuity >= 0.70) {
            return 15;
        }
        return 0;
    }

    /**
     * Sample 1:1 native crop inside smooth facial skin zones to detect RGB Moire & subpixel aliasing.
     */
    function computeMoireScore(videoEl, cropCanvas, cropCtx, faceBox) {
        const vw = videoEl.videoWidth || 640;
        const vh = videoEl.videoHeight || 480;

        const sx = clamp(Math.floor(faceBox.x + faceBox.width * 0.18), 0, Math.max(0, vw - 24));
        const sy = clamp(Math.floor(faceBox.y + faceBox.height * 0.18), 0, Math.max(0, vh - 24));
        const sw = clamp(Math.floor(faceBox.width * 0.64), 24, Math.min(CROP_MAX, vw - sx));
        const sh = clamp(Math.floor(faceBox.height * 0.64), 24, Math.min(CROP_MAX, vh - sy));

        if (sw < 24 || sh < 24) return 0;

        cropCanvas.width = sw;
        cropCanvas.height = sh;
        cropCtx.drawImage(videoEl, sx, sy, sw, sh, 0, 0, sw, sh);

        const imgData = cropCtx.getImageData(0, 0, sw, sh).data;
        let smoothCount = 0;
        let reversalCount = 0;
        let highChromaCount = 0;

        for (let y = 0; y < sh; y += 2) {
            const rowOffset = y * sw;
            for (let x = 0; x <= sw - 3; x++) {
                const i0 = (rowOffset + x) * 4;
                const i1 = i0 + 4;
                const i2 = i0 + 8;

                const l0 = luma(imgData[i0], imgData[i0 + 1], imgData[i0 + 2]);
                const l1 = luma(imgData[i1], imgData[i1 + 1], imgData[i1 + 2]);
                const l2 = luma(imgData[i2], imgData[i2 + 1], imgData[i2 + 2]);

                // Only analyze smooth-luminance areas so beard/eyebrows/glasses edges are ignored
                if (Math.abs(l1 - l0) < 18 && Math.abs(l2 - l1) < 18) {
                    smoothCount++;

                    const rg0 = imgData[i0] - imgData[i0 + 1];
                    const rg1 = imgData[i1] - imgData[i1 + 1];
                    const rg2 = imgData[i2] - imgData[i2 + 1];

                    const bg0 = imgData[i0 + 2] - imgData[i0 + 1];
                    const bg1 = imgData[i1 + 2] - imgData[i1 + 1];
                    const bg2 = imgData[i2 + 2] - imgData[i2 + 1];

                    const dRg1 = rg1 - rg0;
                    const dRg2 = rg2 - rg1;
                    const dBg1 = bg1 - bg0;
                    const dBg2 = bg2 - bg1;

                    if (dRg1 * dRg2 < -18 || dBg1 * dBg2 < -18) {
                        reversalCount++;
                    }
                    if (Math.abs(dRg1) + Math.abs(dBg1) > 14) {
                        highChromaCount++;
                    }
                }
            }
        }

        if (smoothCount < 40) return 0;

        const moireRippleRate = reversalCount / smoothCount;
        const chromaNoiseRate = highChromaCount / smoothCount;

        if (moireRippleRate >= 0.22 || (moireRippleRate >= 0.14 && chromaNoiseRate >= 0.28)) {
            return 30;
        }
        if (moireRippleRate >= 0.14 || chromaNoiseRate >= 0.22) {
            return 18;
        }
        if (moireRippleRate >= 0.09) {
            return 10;
        }
        return 0;
    }

    /**
     * Detect glass specular reflection & emissive cool-blue screen cast on workCanvas.
     */
    function computeGlareAndColorScores(data, fx, fy, fw, fh) {
        // Glare check inside 1.25x expanded viewport
        const gx0 = clamp(Math.floor(fx - fw * 0.125), 0, WORK_W - 1);
        const gx1 = clamp(Math.ceil(fx + fw * 1.125), gx0 + 1, WORK_W);
        const gy0 = clamp(Math.floor(fy - fh * 0.125), 0, WORK_H - 1);
        const gy1 = clamp(Math.ceil(fy + fh * 1.125), gy0 + 1, WORK_H);

        let glarePixels = 0;
        let glareTotal = 0;
        for (let y = gy0; y < gy1; y++) {
            for (let x = gx0; x < gx1; x++) {
                const idx = (y * WORK_W + x) * 4;
                const r = data[idx];
                const g = data[idx + 1];
                const b = data[idx + 2];
                const maxC = Math.max(r, g, b);
                const minC = Math.min(r, g, b);
                glareTotal++;
                if (r > 238 && g > 238 && b > 238 && (maxC - minC) < 14) {
                    glarePixels++;
                }
            }
        }

        const glareRatio = glareTotal > 0 ? glarePixels / glareTotal : 0;
        let glareScore = 0;
        if (glareRatio >= 0.055) glareScore = 22;
        else if (glareRatio >= 0.025) glareScore = 13;
        else if (glareRatio >= 0.012) glareScore = 6;

        // Color temperature & bluish black floor inside face box
        const cx0 = clamp(Math.floor(fx), 0, WORK_W - 1);
        const cx1 = clamp(Math.ceil(fx + fw), cx0 + 1, WORK_W);
        const cy0 = clamp(Math.floor(fy), 0, WORK_H - 1);
        const cy1 = clamp(Math.ceil(fy + fh), cy0 + 1, WORK_H);

        let sumR = 0;
        let sumB = 0;
        let midToneCount = 0;
        let darkCount = 0;
        let bluishDarkCount = 0;

        for (let y = cy0; y < cy1; y++) {
            for (let x = cx0; x < cx1; x++) {
                const idx = (y * WORK_W + x) * 4;
                const r = data[idx];
                const g = data[idx + 1];
                const b = data[idx + 2];
                const lum = luma(r, g, b);

                if (lum >= 60 && lum <= 220) {
                    sumR += r;
                    sumB += b;
                    midToneCount++;
                }
                if (lum < 75) {
                    darkCount++;
                    if (b > r + 7) {
                        bluishDarkCount++;
                    }
                }
            }
        }

        let colorScore = 0;
        if (midToneCount > 30 && sumR > 0) {
            const blueRedRatio = sumB / sumR;
            if (blueRedRatio >= 0.94) colorScore += 12;
            else if (blueRedRatio >= 0.87) colorScore += 6;
        }
        if (darkCount > 25) {
            const bluishDarkRatio = bluishDarkCount / darkCount;
            if (bluishDarkRatio >= 0.25) colorScore += 10;
            else if (bluishDarkRatio >= 0.14) colorScore += 5;
        }

        return {
            glareScore,
            colorScore: Math.min(20, colorScore),
        };
    }

    function createSession() {
        const workCanvas = document.createElement('canvas');
        workCanvas.width = WORK_W;
        workCanvas.height = WORK_H;
        const workCtx = workCanvas.getContext('2d', { willReadFrequently: true });

        const cropCanvas = document.createElement('canvas');
        const cropCtx = cropCanvas.getContext('2d', { willReadFrequently: true });

        const session = {
            screenHitStreak: 0,
            clearStreak: 0,
            prevRowMeans: null,
            prevFaceLuma: null,
            lastResult: {
                score: 0,
                confirmed: false,
                isScreenDetected: false,
                suspected: false,
                reasons: [],
                metrics: {
                    bezelScore: 0,
                    moireScore: 0,
                    glareScore: 0,
                    colorScore: 0,
                    temporalScore: 0,
                },
            },

            reset() {
                this.screenHitStreak = 0;
                this.clearStreak = 0;
                this.prevRowMeans = null;
                this.prevFaceLuma = null;
                this.lastResult = {
                    score: 0,
                    confirmed: false,
                    isScreenDetected: false,
                    suspected: false,
                    reasons: [],
                    metrics: {
                        bezelScore: 0,
                        moireScore: 0,
                        glareScore: 0,
                        colorScore: 0,
                        temporalScore: 0,
                    },
                };
            },

            analyzeFrame(videoEl, faceBox) {
                if (!videoEl || !workCtx || videoEl.videoWidth <= 0 || videoEl.videoHeight <= 0) {
                    return this.lastResult;
                }

                const vw = videoEl.videoWidth;
                const vh = videoEl.videoHeight;

                const effectiveBox = faceBox && faceBox.width > 10 && faceBox.height > 10
                    ? faceBox
                    : {
                        x: vw * 0.25,
                        y: vh * 0.18,
                        width: vw * 0.50,
                        height: vh * 0.64,
                    };

                workCtx.drawImage(videoEl, 0, 0, WORK_W, WORK_H);
                const imgData = workCtx.getImageData(0, 0, WORK_W, WORK_H).data;

                const scaleX = WORK_W / vw;
                const scaleY = WORK_H / vh;
                const fx = effectiveBox.x * scaleX;
                const fy = effectiveBox.y * scaleY;
                const fw = effectiveBox.width * scaleX;
                const fh = effectiveBox.height * scaleY;

                // Signal 1: Rectangular bezel / frame border around face
                const bezelScore = computeBezelScore(imgData, fx, fy, fw, fh);

                // Signal 2: Native 1:1 crop chromatic Moire & subpixel ripple
                const moireScore = computeMoireScore(videoEl, cropCanvas, cropCtx, effectiveBox);

                // Signals 3 & 4: Specular glass glare & cool-blue emissive screen cast
                const { glareScore, colorScore } = computeGlareAndColorScores(imgData, fx, fy, fw, fh);

                // Signal 5: Temporal PWM banding & frozen feed
                let temporalScore = 0;
                const ryStart = clamp(Math.floor(fy), 0, WORK_H - 1);
                const ryEnd = clamp(Math.ceil(fy + fh), ryStart + 4, WORK_H);
                const rxStart = clamp(Math.floor(fx + fw * 0.15), 0, WORK_W - 1);
                const rxEnd = clamp(Math.ceil(fx + fw * 0.85), rxStart + 4, WORK_W);

                const curRowMeans = [];
                const curFaceLuma = [];
                for (let y = ryStart; y < ryEnd; y++) {
                    let rowSum = 0;
                    let rowCount = 0;
                    for (let x = rxStart; x < rxEnd; x += 2) {
                        const lVal = getLumaAt(imgData, WORK_W, x, y);
                        rowSum += lVal;
                        rowCount++;
                        if ((y - ryStart) % 3 === 0) {
                            curFaceLuma.push(lVal);
                        }
                    }
                    curRowMeans.push(rowCount > 0 ? rowSum / rowCount : 0);
                }

                if (
                    this.prevRowMeans &&
                    this.prevRowMeans.length === curRowMeans.length &&
                    curRowMeans.length >= 12
                ) {
                    const diffs = [];
                    let sumDiff = 0;
                    for (let i = 0; i < curRowMeans.length; i++) {
                        const d = curRowMeans[i] - this.prevRowMeans[i];
                        diffs.push(d);
                        sumDiff += d;
                    }
                    const meanShift = sumDiff / diffs.length;

                    let varSum = 0;
                    let alternations = 0;
                    for (let i = 0; i < diffs.length; i++) {
                        const centered = diffs[i] - meanShift;
                        varSum += centered * centered;
                        if (i > 0) {
                            const prevCentered = diffs[i - 1] - meanShift;
                            if (centered * prevCentered < -1.0) {
                                alternations++;
                            }
                        }
                    }
                    const bandStd = Math.sqrt(varSum / diffs.length);
                    const bandAlternations = alternations / Math.max(1, diffs.length - 1);

                    if (Math.abs(meanShift) < 8 && bandStd >= 4.5 && bandAlternations >= 0.45) {
                        temporalScore += 18;
                    }
                }

                if (
                    this.prevFaceLuma &&
                    this.prevFaceLuma.length === curFaceLuma.length &&
                    curFaceLuma.length >= 30
                ) {
                    let absDiffSum = 0;
                    for (let i = 0; i < curFaceLuma.length; i++) {
                        absDiffSum += Math.abs(curFaceLuma[i] - this.prevFaceLuma[i]);
                    }
                    const meanAbsDiff = absDiffSum / curFaceLuma.length;
                    if (meanAbsDiff < 0.65) {
                        temporalScore += 16;
                    }
                }
                temporalScore = Math.min(25, temporalScore);

                this.prevRowMeans = curRowMeans;
                this.prevFaceLuma = curFaceLuma;

                const score = Math.min(100, bezelScore + moireScore + glareScore + colorScore + temporalScore);

                const reasons = [];
                if (bezelScore >= 12) reasons.push('Terdeteksi bingkai / tepi layar persegi');
                if (moireScore >= 12) reasons.push('Terdeteksi pola piksel / moire layar digital');
                if (glareScore >= 12) reasons.push('Terdeteksi pantulan cahaya kaca layar');
                if (colorScore >= 12) reasons.push('Spektrum cahaya biru khas layar monitor/HP');
                if (temporalScore >= 12) reasons.push('Terdeteksi kedipan layar / gambar statis');

                if (score >= 52 || bezelScore >= 36) {
                    this.screenHitStreak++;
                    this.clearStreak = 0;
                } else if (score < 38) {
                    this.clearStreak++;
                    if (this.clearStreak >= 2) {
                        this.screenHitStreak = 0;
                    }
                }

                const confirmed = this.screenHitStreak >= 2 || score >= 68;

                this.lastResult = {
                    score,
                    confirmed,
                    isScreenDetected: confirmed,
                    suspected: score >= 52,
                    reasons,
                    metrics: {
                        bezelScore,
                        moireScore,
                        glareScore,
                        colorScore,
                        temporalScore,
                    },
                };

                return this.lastResult;
            },

            async analyzeCaptureBurst(videoEl, faceBox, frames = 3, intervalMs = 110) {
                let maxScore = 0;
                let totalScore = 0;
                const reasonSet = new Set();
                let bestMetrics = this.lastResult.metrics;

                for (let i = 0; i < frames; i++) {
                    if (i > 0) {
                        await new Promise((resolve) => setTimeout(resolve, intervalMs));
                    }
                    const res = this.analyzeFrame(videoEl, faceBox);
                    totalScore += res.score;
                    if (res.score >= maxScore) {
                        maxScore = res.score;
                        bestMetrics = res.metrics;
                    }
                    res.reasons.forEach((r) => reasonSet.add(r));
                }

                const avgScore = Math.round(totalScore / Math.max(1, frames));
                const blocked = maxScore >= 64 || avgScore >= 52;

                return {
                    blocked,
                    maxScore,
                    avgScore,
                    reasons: Array.from(reasonSet),
                    metrics: bestMetrics,
                };
            },
        };

        return session;
    }

    function dist2d(a, b) {
        const dx = a.x - b.x;
        const dy = a.y - b.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    function eyeEAR(eye) {
        if (!eye || eye.length < 6) return 0.3;
        const v1 = dist2d(eye[1], eye[5]);
        const v2 = dist2d(eye[2], eye[4]);
        const h = Math.max(1, dist2d(eye[0], eye[3]));
        return (v1 + v2) / (2.0 * h);
    }

    function computeEAR(landmarks) {
        if (!landmarks) return 0.3;
        const left = typeof landmarks.getLeftEye === 'function'
            ? landmarks.getLeftEye()
            : (landmarks.positions ? landmarks.positions.slice(36, 42) : null);
        const right = typeof landmarks.getRightEye === 'function'
            ? landmarks.getRightEye()
            : (landmarks.positions ? landmarks.positions.slice(42, 48) : null);

        return (eyeEAR(left) + eyeEAR(right)) / 2.0;
    }

    function computeYawRatio(landmarks) {
        if (!landmarks || !landmarks.positions || landmarks.positions.length < 31) {
            return 0.5;
        }
        const pts = landmarks.positions;
        const jaw0 = pts[0];
        const jaw16 = pts[16];
        const noseTip = pts[30];
        const jawSpan = Math.max(1, jaw16.x - jaw0.x);
        return (noseTip.x - jaw0.x) / jawSpan;
    }

    function pickRandomChallenge(excludeType) {
        const pool = excludeType
            ? CHALLENGES.filter((c) => c.type !== excludeType)
            : CHALLENGES;
        const picked = pool[Math.floor(Math.random() * pool.length)] || CHALLENGES[0];
        return { ...picked };
    }

    global.AttendanceScreenDetector = {
        createSession,
        computeEAR,
        computeYawRatio,
        pickRandomChallenge,
    };
})(window);
