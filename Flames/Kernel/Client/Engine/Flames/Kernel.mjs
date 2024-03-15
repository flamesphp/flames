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
    
    
  > Initializing...`, 'color: #ffb158; font-size: 14px;');

window.Flames = (window.Flames || {});
Flames.Internal = (Flames.Internal || {});
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

import { PhpBase } from './kernel/base.mjs';
import PhpBinary from './kernel/web.mjs';
import PHP from "./kernel/web.mjs";

export class PhpWeb extends PhpBase
{
    constructor(args = {})
    {
        super(PhpBinary, args);
    }

    run(phpCode)
    {
        return this.binary.then(php => {

            const sync = !php.persist
                ? Promise.resolve()
                : new Promise(accept => php.FS.syncfs(true, err => {
                    if(err) console.warn(err);
                    accept();
                }));

            const run = sync.then(() => super.run(phpCode));

            if(!php.persist)
            {
                return run;
            }

            return run.then(() => new Promise(accept => php.FS.syncfs(false, err => {
                if(err) console.warn(err);
                accept(run);
            })));
        })
            .finally(() => this.flush());
    }

    exec(phpCode)
    {
        return this.binary.then(php => {
            const sync = new Promise(accept => php.FS.syncfs(true, err => {
                if(err) console.warn(err);
                accept();
            }));

            const run = sync.then(() =>super.exec(phpCode));

            return run.then(() => new Promise(accept => php.FS.syncfs(false, err => {
                if(err) console.warn(err);
                accept(run);
            })));
        })
            .finally(() => this.flush());
    }
}

const runPhpScriptTag = element => {

    const tags = {stdin: null, stdout: null, stderr: null};

    if(element.hasAttribute('data-stdout'))
    {
        tags.stdout = document.querySelector(element.getAttribute('data-stdout'));
    }

    if(element.hasAttribute('data-stderr'))
    {
        tags.stderr = document.querySelector(element.getAttribute('data-stderr'));
    }

    if(element.hasAttribute('data-stdin'))
    {
        tags.stdin = document.querySelector(element.getAttribute('data-stdin'));
    }

    let stdout = '';
    let stderr = '';
    let ran = false;

    let getCode = Promise.resolve(element.innerText);

    if(element.hasAttribute('src'))
    {
        getCode = fetch(element.getAttribute('src')).then(response => response.text());
    }

    let getInput = Promise.resolve('');

    if(tags.stdin)
    {
        getInput = Promise.resolve(tags.stdin.innerText);

        if(tags.stdin.hasAttribute('src'))
        {
            getInput = fetch(tags.stdin.getAttribute('src')).then(response => response.text());
        }
    }

    const getAll = Promise.all([getCode, getInput]);

    getAll.then(([code, input,]) => {
        const php = new PhpWeb;

        php.inputString(input);

        const outListener = event => {

            stdout += event.detail;

            if(ran && tags.stdout)
            {
                tags.stdout.innerHTML = stdout;
            }
        };

        const errListener = event => {

            stderr += event.detail;

            if(ran && tags.stderr)
            {
                tags.stderr.innerHTML = stderr;
            }
        };

        php.addEventListener('output', outListener);
        php.addEventListener('error',  errListener);

        php.addEventListener('ready', () => {
            php.run(code)
                .then(exitCode => function() {} ) //console.log(exitCode))
                .catch(error => console.error(error))
                .finally(() => {
                    ran = true;
                    php.flush();
                    if (stdout.includes('Parse error: syntax error')) {
                        console.error(stdout);
                        return;
                    }
                    if (stdout !== '') {
                        console.log(stdout);
                    }
                    tags.stdout && (tags.stdout.innerHTML = stdout);
                    tags.stderr && (tags.stderr.innerHTML = stderr);
                });
        });

        // const observer = new MutationObserver((mutations, observer) => {
        // 	for(const mutation of mutations)
        // 	{
        // 		for(const addedNode of mutation.addedNodes)
        // 		{
        // 			console.log(addedNode);
        // 			// php.inputString(addedNode);
        // 			// php.run(code)
        // 			// .then(exitCode => console.log(exitCode))
        // 			// .catch(error => console.warn(error))
        // 			// .finally(() => {
        // 			// 	php.flush();
        // 			// 	tags.stdout && (tags.stdout.innerHTML = stdout);
        // 			// 	tags.stderr && (tags.stderr.innerHTML = stderr);
        // 			// 	php.removeEventListener('output', outListener);
        // 			// 	php.removeEventListener('error',  errListener);
        // 			// });
        // 		}
        // 	}
        // });

        // observer.observe(element, {childList: true, subtree: true});
    });
}

const phpSelector = 'script[type="text/php"]';

export const runPhpTags = (doc) => {

    const phpNodes = doc.querySelectorAll(phpSelector);

    for(const phpNode of phpNodes)
    {
        const code = phpNode.innerText.trim();

        runPhpScriptTag(phpNode);
    }

    const observer = new MutationObserver((mutations, observer) => {
        for(const mutation of mutations)
        {
            for(const addedNode of mutation.addedNodes)
            {
                if(!addedNode.matches || !addedNode.matches(phpSelector))
                {
                    continue;
                }

                runPhpScriptTag(addedNode);
            }
        }
    });

    observer.observe(document.body.parentElement, {childList: true, subtree: true});
}

export const runFlames = () => {

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

        console.log('%c  > Core loading...', 'color: #ffb158; font-size: 14px;');
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
            console.log('%c  > Loading ' + client[0] + '.', 'color: #ffb158; font-size: 14px;');
            window.PHP.eval(decode);
        }

        console.log("%c  > Route loading...\n\r", 'color: #ffb158; font-size: 14px;');
        window.PHP.eval('<?php \\Flames\\Kernel\\Client::run(); ?>');
    });
}