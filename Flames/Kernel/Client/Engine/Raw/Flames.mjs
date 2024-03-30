console.log(`%c 
    ███████╗██╗      █████╗ ███╗   ███╗███████╗███████╗
    ██╔════╝██║     ██╔══██╗████╗ ████║██╔════╝██╔════╝
    █████╗  ██║     ███████║██╔████╔██║█████╗  ███████╗
    ██╔══╝  ██║     ██╔══██║██║╚██╔╝██║██╔══╝  ╚════██║
    ██║     ███████╗██║  ██║██║ ╚═╝ ██║███████╗███████║
    ╚═╝     ╚══════╝╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝╚══════╝
    
    𝗖𝗿𝗲𝗮𝘁𝗲𝗱 𝗯𝘆 𝗚𝗮𝗯𝗿𝗶𝗲𝗹 '𝗞𝗮𝘇𝘇' 𝗠𝗼𝗿𝗴𝗮𝗱𝗼
    Github: https://github.com/flamesphp/flames
    Docs:   https://flamesphp.com
    
    
  > Initializing`, 'color: #ffb158; font-size: 14px;');

Flames.Internal.char = 'ロ';

Flames.Internal.uid = 0;

Flames.Internal.generateUid = (function(uid) {
    uid = uid.toString();

    var newUid = md5(uid);
    newUid = newUid.substring(0, 18 - uid.length);

    for (var i = 0; i < uid.length; i++) {
        if (uid[i] === '0') {
            newUid += 'a';
        } else if (uid[i] === '1') {
            newUid += 'e';
        } else if (uid[i] === '2') {
            newUid += 'd';
        } else if (uid[i] === '3') {
            newUid += 'b';
        } else if (uid[i] === '4') {
            newUid += 'c';
        } else if (uid[i] === '5') {
            newUid += 'f';
        } else if (uid[i] === '6') {
            newUid += '3';
        } else if (uid[i] === '7') {
            newUid += '2';
        } else if (uid[i] === '8') {
            newUid += '1';
        } else if (uid[i] === '9') {
            newUid += '0';
        }
    }
    return newUid;
});

Flames.Internal.HttpResponse = [];

Flames.Internal.Http = (function(data) {
    var data = JSON.parse(data);

    var params = null;

    if (data.header.form_params !== undefined) {
        params = data.header.form_params;
        data.header.form_params = undefined;
    }

    Flames.Internal.HttpAxios({
        method: data.method,
        url: data.url,
        data: params,
        responseType: 'text',
        headers: data.header
    }).then(function (_response) {
        var headers = [];
        var key;
        for (key in _response.headers) {
            if (_response.headers.hasOwnProperty(key)) {
                headers[headers.length] = [key, _response.headers[key]];
            }
        }

        var response = //JSON.stringify(
            {
                status: _response.status,
                body: _response.data,
                header: headers
            }

        window.PHP.eval('<?php Flames\\Http\\Client::callback(' + data.id + ', \'' + JSON.stringify(response) + '\'); ?>');
    }).catch(function (error) {
        var response = JSON.stringify({
            status: 'error',
            message: error.message,
            header: []
        });

        window.PHP.eval('<?php Flames\\Http\\Client::callback(' + data.id + ', \'' + response + '\'); ?>');
    });
});

(function() {
    new MutationObserver(function() {
        Flames.Internal.verifyFS();
    }).observe(document.querySelector('body'), {
        childList: true,
        subtree: true,
        attributes: true
    });
})();

Flames.Internal.onClick = (function(uid) {
    var event = Flames.Internal.Build.click[uid];
    if (event === null || event === undefined) {
        return;
    }

    var _class = decodeURIComponent(event[0]);
    var method = event[1];
    window.PHP.eval('<?php \\Flames\\Kernel\\Client\\Dispatch::getInstance(\'' + _class + '\')->' + method + '(\\Flames\\Element::query(\'[' + Flames.Internal.char + 'click="' + uid + '"]\')); ?>');
});

Flames.Internal.verifyUid = (function(element) {
    if (element.getAttribute(Flames.Internal.char + 'uid') === null) {
        Flames.Internal.uid++;
        element.setAttribute(Flames.Internal.char + 'uid', Flames.Internal.generateUid(Flames.Internal.uid));
    }
});

Flames.Internal.verifyFS = (function() {
    var elements = document.querySelectorAll('*');
    for (var i = 0; i < elements.length; i++) {
        var element = elements[i];

        if (element.getAttribute('@click') !== null) {
            element.setAttribute(Flames.Internal.char + 'click', element.getAttribute('@click'));
            element.removeAttribute('@click');
            Flames.Internal.verifyUid(element);
            element.addEventListener('click', function(event) {
                event.preventDefault();
                Flames.Internal.onClick(event.target.getAttribute(Flames.Internal.char + 'click'));
            });
        }


    }
});
Flames.Internal.verifyFS();

window.php = function (code) {
    window.PHP.eval('<?php ' + code + ' ?>');
}

const runFlames = () => {
    let stdout = '';
    let stderr = '';
    let php = new PhpWeb;

    window.PHP = (window.PHP || {});
    window.PHP.internal = php;
    window.PHP.eval = function(code) {
        window.PHP.internal.run(code)
            .then(exitCode => function() {} ) //console.log(exitCode))
            .catch(error => console.error(error))
            .finally(() => {
                php.flush();
                if (stdout.includes('Parse error: syntax error') ||
                    stdout.includes('Fatal error: ')
                ) {
                    console.error(stdout);
                    stdout = '';
                    return;
                }
                if (stdout !== '') {
                    console.log(stdout);
                    stdout = '';
                }
            });
    }

    const outListener = event => {
        stdout += event.detail;
    };

    const errListener = event => {

        stderr += event.detail;
    };

    php.addEventListener('output', outListener);
    php.addEventListener('error',  errListener);
    php.addEventListener('ready', () => {

        console.log('%c  > Core loading', 'color: #ffb158; font-size: 14px;');
        if (Flames.Internal.Build === undefined) {
            console.log('%c  > Core not compiled, please run command: php bin build:assets', 'color: #ff6666; font-size: 14px;');
            return;
        }

        var coreLength = Flames.Internal.Build.core.length;
        for (var i = 0; i < coreLength; i++) {
            var core = Flames.Internal.Build.core[i];
            var decode = atob(core);
            window.PHP.eval(decode);
        }

        var clientLength = Flames.Internal.Build.client.length;
        for (var i = 0; i < clientLength; i++) {
            var client = Flames.Internal.Build.client[i];
            var decode = atob(client[1]);
            console.log('%c  > Loading', 'color: #ffb158; font-size: 14px;', client[0]);
            window.PHP.eval(decode);
        }

        console.log("%c  > Flames loaded successfully\n\r", 'color: #ffb158; font-size: 14px;');
        window.PHP.eval('<?php \\Flames\\Kernel\\Client\\Dispatch::run(); ?>');
    });
}

runFlames();