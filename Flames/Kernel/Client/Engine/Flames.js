window.Flames = (window.Flames || {});

window.Flames.onBoot = function() {
    window.Flames.Internal.mockLog = {};
    window.Flames.Internal.mockLog.log = function() {};
    window.Flames.Internal.mockLog.error = function() {};
    window.Flames.Internal.mockLog.warn = function() {};
    window.Flames.Internal.log = false;
    window.Flames.Internal.environment = decodeURIComponent('{{ environment }}');
    window.Flames.Internal.dumpLocalPath = decodeURIComponent('{{ dumpLocalPath }}');
    window.Flames.Internal.autoBuild = '{{ autoBuild }}';
    window.Flames.Internal.composer = '{{ composer }}';
    window.Flames.Internal.swfExtension = '{{ swfExtension }}';
    window.Flames.Internal.onErrorListener = function(error, line, file, code, traceString, trace) {
        console.error(error);
        console.error(line);
        console.error(file);
        console.error(code);
        console.error(traceString);
        console.error(Flames.Internal.unserialize(trace));
    };

    window.Flames.Internal.onSuccessListener = function(data) {
        if (Flames.Internal.log === false) {
            return;
        }
        console.log(data);
    };

    window.Flames.Internal.dump = function(param1, param2) {
        if (window.Flames.Internal.dumpLocalPath === undefined) {
            param2 = param2.replace('{DUMP_LOCAL_PATH}', '');
            dump(param1, param2);
        } else {
            param2 = param2.replace('{DUMP_LOCAL_PATH}', Flames.Internal.dumpLocalPath);
            dump(param1, param2);
        }
    };

    window.dump = console.log;
    window.Flames.Internal.char = 'ロ';
    window.Flames.Internal.uid = 0;
    window.Flames.Internal.hashidfy = new Flames.Internal.Hashid('', 14,'abcdefghijklmnopqrstuvwxyz0123456789','');
    window.Flames.Internal.generateUid = (function() {
        window.Flames.Internal.uid += 1;
        return Flames.Internal.hashidfy.encode([Flames.Internal.uid]);
    });

    Array.prototype.toPhpSerialize = function() { return Flames.Internal.serialize(this); }
    DOMTokenList.prototype.toArray = function() { return this.value.split(' '); }
    NodeList.prototype.toPhpSerializeUids = function() {
        var array = [];
        for (var key in this) {
            if (this.hasOwnProperty(key)) {
                var element = this[key];
                var uid = element.getAttribute(Flames.Internal.char + 'uid');
                if (uid === null) {
                    uid = Flames.Internal.generateUid();
                    element.setAttribute(Flames.Internal.char + 'uid', uid);
                }
                array[array.length] = uid;
            }
        }
        return array.toPhpSerialize();
    }

    window.Flames.onReady();
}

window.Flames.onUnsupported = function() {
    '{{ unsupported }}';
};