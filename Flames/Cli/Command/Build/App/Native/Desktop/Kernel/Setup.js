// Autogenerated by Flames
// Github: https://github.com/flamesphp/flames
// Docs:   https://flamesphp.com

const nodeOs = require('node:os');
const info = {};

exports.setup = () => {
    info.platform = nodeOs.platform();
    info.release = nodeOs.release();
    info.arch = nodeOs.arch();
    info.type = nodeOs.type();
    info.machine = nodeOs.machine();
    info.version = nodeOs.version();
};

exports.getInfo = () => {
    return info;
}
