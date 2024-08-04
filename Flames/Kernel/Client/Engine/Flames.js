window.Flames = (window.Flames || {});

Flames.onBoot = function() {
    Flames.Internal.log = false;
    Flames.Internal.environment = decodeURIComponent('{{ environment }}');
    Flames.Internal.autoBuild = '{{ autobuild }}';
    Flames.Internal.onErrorListener = function(error, line, file, code, traceString, trace) {
        console.error(error);
        console.error(line);
        console.error(file);
        console.error(code);
        console.error(traceString);
        console.error(Flames.Internal.unserialize(trace));
    };

    Flames.Internal.onSuccessListener = function(data) {
        if (Flames.Internal.log === false) {
            return;
        }
        console.log(data);
    };

    window.dump = console.log;
    Flames.Internal.char = 'ロ';
    Flames.Internal.uid = 0;
    Flames.Internal.hashidfy = new Flames.Internal.Hashid('', 14,'abcdefghijklmnopqrstuvwxyz0123456789','');
    Flames.Internal.generateUid = (function() {
        Flames.Internal.uid += 1;
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

    Flames.onReady();
}

Flames.onUnsupported = function() {
    '{{ unsupported }}';
};