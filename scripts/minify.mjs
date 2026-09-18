import fs from 'fs';
import path from 'path';

function minifyCSS(source) {
    let out = '';
    let i = 0;
    const len = source.length;
    let inSingleQuote = false;
    let inDoubleQuote = false;
    let inComment = false;
    let inUrl = false;
    let inCalc = false;

    while (i < len) {
        const c = source[i];
        const next = i + 1 < len ? source[i + 1] : '';

        // Comments
        if (!inSingleQuote && !inDoubleQuote && !inComment && c === '/' && next === '*') {
            inComment = true;
            i += 2;
            continue;
        }
        if (inComment) {
            if (c === '*' && next === '/') {
                inComment = false;
                i += 2;
            } else {
                i++;
            }
            continue;
        }

        // Quotes
        if (!inComment) {
            if (c === "'" && !inDoubleQuote && (i === 0 || source[i - 1] !== '\\')) {
                inSingleQuote = !inSingleQuote;
            } else if (c === '"' && !inSingleQuote && (i === 0 || source[i - 1] !== '\\')) {
                inDoubleQuote = !inDoubleQuote;
            }
        }

        out += c;
        i++;
    }

    // Now safely collapse CSS whitespace outside of strings
    let res = '';
    inSingleQuote = false;
    inDoubleQuote = false;
    let calcDepth = 0;

    for (let j = 0; j < out.length; j++) {
        const c = out[j];
        if (c === "'" && !inDoubleQuote && (j === 0 || out[j - 1] !== '\\')) {
            inSingleQuote = !inSingleQuote;
            res += c;
            continue;
        }
        if (c === '"' && !inSingleQuote && (j === 0 || out[j - 1] !== '\\')) {
            inDoubleQuote = !inDoubleQuote;
            res += c;
            continue;
        }

        if (inSingleQuote || inDoubleQuote) {
            res += c;
            continue;
        }

        // Track calc depth
        if (out.slice(j, j + 5).toLowerCase() === 'calc(') {
            calcDepth++;
        } else if (calcDepth > 0 && c === ')') {
            calcDepth--;
        }

        // Whitespace
        if (/\s/.test(c)) {
            // Keep single space if needed
            const last = res.length > 0 ? res[res.length - 1] : '';
            const nextChar = j + 1 < out.length ? out[j + 1] : '';

            if (calcDepth > 0) {
                // In calc, keep spaces around operators
                if (res.length > 0 && !/\s/.test(last)) {
                    res += ' ';
                }
            } else {
                // Not in calc - only add space if between word characters or after : in properties
                if (/[a-zA-Z0-9_\-)]/.test(last) && /[a-zA-Z0-9_\-(#!.]/.test(nextChar)) {
                    res += ' ';
                }
            }
            continue;
        }

        // Remove spaces before/after punctuation when not in quotes/calc
        if (calcDepth === 0) {
            if (c === '{' || c === '}' || c === ';' || c === ',') {
                if (res.endsWith(' ')) {
                    res = res.slice(0, -1);
                }
            }
        }

        res += c;
    }

    return res.trim();
}

function minifyJS(source) {
    let out = '';
    let i = 0;
    const len = source.length;
    let inSingleQuote = false;
    let inDoubleQuote = false;
    let inBacktick = false;
    let inLineComment = false;
    let inBlockComment = false;

    while (i < len) {
        const c = source[i];
        const next = i + 1 < len ? source[i + 1] : '';
        const prev = i > 0 ? source[i - 1] : '';

        // Check comment states
        if (!inSingleQuote && !inDoubleQuote && !inBacktick) {
            if (!inLineComment && !inBlockComment) {
                if (c === '/' && next === '/') {
                    inLineComment = true;
                    i += 2;
                    continue;
                }
                if (c === '/' && next === '*') {
                    inBlockComment = true;
                    i += 2;
                    continue;
                }
            }
        }

        if (inLineComment) {
            if (c === '\n' || c === '\r') {
                inLineComment = false;
                out += '\n'; // Preserve newline for ASI safety
            }
            i++;
            continue;
        }

        if (inBlockComment) {
            if (c === '*' && next === '/') {
                inBlockComment = false;
                i += 2;
            } else {
                i++;
            }
            continue;
        }

        // Quotes
        if (!inLineComment && !inBlockComment) {
            if (c === "'" && !inDoubleQuote && !inBacktick) {
                if (prev !== '\\' || (i > 1 && source[i - 2] === '\\')) {
                    inSingleQuote = !inSingleQuote;
                }
            } else if (c === '"' && !inSingleQuote && !inBacktick) {
                if (prev !== '\\' || (i > 1 && source[i - 2] === '\\')) {
                    inDoubleQuote = !inDoubleQuote;
                }
            } else if (c === '`' && !inSingleQuote && !inDoubleQuote) {
                if (prev !== '\\' || (i > 1 && source[i - 2] === '\\')) {
                    inBacktick = !inBacktick;
                }
            }
        }

        out += c;
        i++;
    }

    // Protect template literals (including nested ones) during line joining
    const templatePlaceholders = [];
    let protectedStr = '';
    let ti = 0;
    const tlen = out.length;

    while (ti < tlen) {
        if (out[ti] === '`') {
            const start = ti;
            ti++;
            let depth = 1;
            const braceStack = [];

            while (ti < tlen && depth > 0) {
                const c = out[ti];
                if (c === '\\') {
                    ti += 2;
                    continue;
                }
                if (braceStack.length > 0) {
                    if (c === "'" || c === '"') {
                        const q = c;
                        ti++;
                        while (ti < tlen) {
                            if (out[ti] === '\\') { ti += 2; continue; }
                            if (out[ti] === q) { ti++; break; }
                            ti++;
                        }
                        continue;
                    }
                    if (c === '`') {
                        depth++;
                        ti++;
                        continue;
                    }
                    if (c === '{') {
                        braceStack[braceStack.length - 1]++;
                        ti++;
                        continue;
                    }
                    if (c === '}') {
                        braceStack[braceStack.length - 1]--;
                        if (braceStack[braceStack.length - 1] === 0) {
                            braceStack.pop();
                        }
                        ti++;
                        continue;
                    }
                } else {
                    if (c === '$' && ti + 1 < tlen && out[ti + 1] === '{') {
                        braceStack.push(1);
                        ti += 2;
                        continue;
                    }
                    if (c === '`') {
                        depth--;
                        ti++;
                        if (depth === 0) break;
                        continue;
                    }
                }
                ti++;
            }
            const rawTL = out.substring(start, ti);
            const key = `___TL_${templatePlaceholders.length}___`;
            templatePlaceholders.push(rawTL);
            protectedStr += key;
        } else {
            protectedStr += out[ti];
            ti++;
        }
    }

    const lines = protectedStr.split(/\r?\n/);
    let result = '';

    for (let line of lines) {
        const trimmed = line.trim();
        if (!trimmed) continue;
        if (!result) {
            result = trimmed;
            continue;
        }

        const lastChar = result[result.length - 1];
        const firstChar = trimmed[0];

        let sep = '';
        if (")}].,:?=+*/%&|^<>!~-".includes(firstChar) || firstChar === '{') {
            sep = '';
        } else if (/^(else|catch|finally|while|instanceof|in)\b/.test(trimmed)) {
            sep = (lastChar === '}') ? '' : ' ';
        } else if (lastChar === '}') {
            sep = ';';
        } else if (';{}([,:?=+*/%&|^<>!~.-'.includes(lastChar)) {
            sep = '';
        } else {
            sep = ';';
        }

        if (sep === '' && /[a-zA-Z0-9_$]/.test(lastChar) && /[a-zA-Z0-9_$]/.test(firstChar)) {
            sep = ' ';
        }
        if ((lastChar === '+' && firstChar === '+') || (lastChar === '-' && firstChar === '-')) {
            sep = ' ';
        }

        result += sep + trimmed;
    }

    // Protect strings ('...' and "...")
    const stringPlaceholders = [];
    result = result.replace(/('(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*")/gs, (m) => {
        const key = `___STR_${stringPlaceholders.length}___`;
        stringPlaceholders.push(m);
        return key;
    });

    result = result.replace(/\s+/g, ' ');
    result = result.replace(/\s*([\{\}\(\)\[\];,:?=\*\/\%&\|\^<>!~])\s*/g, '$1');
    result = result.replace(/\s*([+\-])\s*/g, '$1');
    result = result.replace(/\+ \+/g, '+ +').replace(/- -/g, '- -');

    // Restore strings
    stringPlaceholders.forEach((s, idx) => {
        result = result.replace(`___STR_${idx}___`, () => s);
    });

    // Restore templates
    templatePlaceholders.forEach((t, idx) => {
        const flattened = t.replace(/\r?\n\s*/g, ' ');
        result = result.replace(`___TL_${idx}___`, () => flattened);
    });

    return result.trim();
}

// Backup and minify targets
const files = [
    { type: 'css', path: 'public/css/frontend.css', backup: 'public/css/frontend.src.css' },
    { type: 'js',  path: 'public/js/frontend.js',   backup: 'public/js/frontend.src.js' },
    { type: 'js',  path: 'public/js/tracking.js',   backup: 'public/js/tracking.src.js' },
    { type: 'css', path: 'public/backend/css/admin-shell.css', backup: 'public/backend/css/admin-shell.src.css' },
    { type: 'js',  path: 'public/backend/js/admin-shell.js',   backup: 'public/backend/js/admin-shell.src.js' }
];

console.log('--- Starting Safe Minification ---');
for (const file of files) {
    if (!fs.existsSync(file.path) && !fs.existsSync(file.backup)) {
        console.log(`Skipping missing file: ${file.path}`);
        continue;
    }
    const sourcePath = fs.existsSync(file.backup) ? file.backup : file.path;
    const original = fs.readFileSync(sourcePath, 'utf8');

    let minified = '';
    if (file.type === 'css') {
        minified = minifyCSS(original);
    } else {
        minified = minifyJS(original);
    }

    fs.writeFileSync(file.path, minified, 'utf8');
    const oldSize = Buffer.byteLength(original, 'utf8');
    const newSize = Buffer.byteLength(minified, 'utf8');
    const saved = (((oldSize - newSize) / oldSize) * 100).toFixed(1);
    console.log(`Minified ${file.path}: ${(oldSize / 1024).toFixed(1)} KB -> ${(newSize / 1024).toFixed(1)} KB (${saved}% smaller)`);
}
console.log('--- Minification Complete ---');
