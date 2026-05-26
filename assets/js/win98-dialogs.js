/* Reemplazo Win98 para los diálogos nativos del navegador.
   - `alert(msg)` se sobreescribe → modal Win98 (mismo contrato void).
   - `win98Confirm(msg, title, onOk, onCancel)` y
     `win98Prompt(msg, defaultVal, onOk, onCancel)` son async (callback).
   - confirm() / prompt() nativos NO se pueden emular sync; siguen siendo
     callables pero recomendamos migrar a las versiones win98. */
(function(){
    if (window.__win98DialogsLoaded) return;
    window.__win98DialogsLoaded = true;

    /* Z-index alto para superar cualquier ventana abierta. El observer de
       windowZ no se mete con elementos que NO tengan clase .window, así que
       inyectamos display directo y dejamos que un z-index altísimo gane. */
    var Z = 999999;

    function build(opts){
        /* opts: { type:'alert|confirm|prompt', title, message, defaultVal,
                   okLabel, cancelLabel, onOk, onCancel } */
        var overlay = document.createElement('div');
        overlay.className = 'w98-dialog-overlay';
        overlay.style.cssText =
            'position:fixed;inset:0;background:rgba(0,0,0,0.25);z-index:' + (Z++) +
            ';display:flex;align-items:center;justify-content:center;';

        var win = document.createElement('div');
        win.className = 'window w98-dialog';
        win.style.cssText = 'min-width:300px;max-width:480px;z-index:' + (Z++) + ';';

        var bar = document.createElement('div');
        bar.className = 'title-bar';
        var barText = document.createElement('div');
        barText.className = 'title-bar-text';
        barText.textContent = opts.title || (opts.type === 'alert' ? 'Aviso' :
                                            opts.type === 'confirm' ? 'Confirmar' : 'Introduce un valor');
        var barCtl = document.createElement('div');
        barCtl.className = 'title-bar-controls';
        var closeBtn = document.createElement('button');
        closeBtn.setAttribute('aria-label', 'Close');
        barCtl.appendChild(closeBtn);
        bar.appendChild(barText); bar.appendChild(barCtl);

        var body = document.createElement('div');
        body.className = 'window-body';
        body.style.cssText = 'padding:12px 14px;';

        var row = document.createElement('div');
        row.style.cssText = 'display:flex;gap:12px;align-items:flex-start;';
        var icon = document.createElement('div');
        icon.textContent = opts.type === 'confirm' ? '❓' :
                          opts.type === 'prompt'  ? '✏️' : 'ℹ️';
        icon.style.cssText = 'font-size:24px;line-height:1;flex:0 0 28px;';
        var msg = document.createElement('div');
        msg.style.cssText = 'flex:1;font-size:11px;white-space:pre-wrap;line-height:1.4;';
        msg.textContent = opts.message || '';
        row.appendChild(icon); row.appendChild(msg);
        body.appendChild(row);

        var input = null;
        if (opts.type === 'prompt') {
            input = document.createElement('input');
            input.type = 'text';
            input.value = (opts.defaultVal != null ? String(opts.defaultVal) : '');
            input.style.cssText = 'width:100%;margin-top:10px;box-sizing:border-box;';
            body.appendChild(input);
        }

        var btns = document.createElement('div');
        btns.style.cssText = 'display:flex;justify-content:flex-end;gap:6px;margin-top:14px;';
        var okBtn = document.createElement('button');
        okBtn.className = 'default';
        okBtn.textContent = opts.okLabel || (opts.type === 'alert' ? 'Aceptar' : 'Aceptar');
        var cancelBtn = null;
        if (opts.type !== 'alert') {
            cancelBtn = document.createElement('button');
            cancelBtn.textContent = opts.cancelLabel || 'Cancelar';
            btns.appendChild(cancelBtn);
        }
        btns.appendChild(okBtn);
        body.appendChild(btns);

        win.appendChild(bar); win.appendChild(body);
        overlay.appendChild(win);
        document.body.appendChild(overlay);

        if (input) setTimeout(function(){ input.focus(); input.select(); }, 30);
        else       setTimeout(function(){ okBtn.focus(); }, 30);

        function close(){
            document.removeEventListener('keydown', onKey, true);
            overlay.remove();
        }
        function doOk(){
            close();
            if (opts.onOk) opts.onOk(input ? input.value : true);
        }
        function doCancel(){
            close();
            if (opts.onCancel) opts.onCancel();
        }
        function onKey(e){
            if (e.key === 'Enter')      { e.preventDefault(); doOk(); }
            else if (e.key === 'Escape'){ e.preventDefault(); doCancel(); }
        }
        document.addEventListener('keydown', onKey, true);

        okBtn.addEventListener('click', doOk);
        if (cancelBtn) cancelBtn.addEventListener('click', doCancel);
        closeBtn.addEventListener('click', doCancel);
    }

    /* API pública (callback-based, async). */
    window.win98Alert = function(message, title, onOk){
        if (typeof title === 'function') { onOk = title; title = undefined; }
        build({ type:'alert', message:String(message), title:title, onOk:onOk });
    };
    window.win98Confirm = function(message, title, onOk, onCancel){
        if (typeof title === 'function') { onCancel = onOk; onOk = title; title = undefined; }
        build({ type:'confirm', message:String(message), title:title, onOk:onOk, onCancel:onCancel });
    };
    window.win98Prompt = function(message, defaultVal, onOk, onCancel, title){
        build({ type:'prompt', message:String(message), defaultVal:defaultVal,
                onOk:onOk, onCancel:onCancel, title:title });
    };

    /* Override del nativo `alert()`: mismo contrato (no devuelve valor), así
       que TODOS los `alert(...)` repartidos por el código heredan el estilo
       Win98 sin tener que tocar cada llamada. */
    window.alert = function(message){ window.win98Alert(message); };
})();
