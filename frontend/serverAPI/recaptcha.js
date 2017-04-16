const getSiteKey = (hostname) => {
    if (hostname.endsWith('cestiadvokati.cz') && hostname !== 'devel.cestiadvokati.cz') {
        return '6Ldw-BsUAAAAAJ35FtswvO1Ar2B2XrkTgmFXs4P6';
    } else {
        return '6LeSHxwUAAAAAIEJhFvajTtV-EhIVf-KLB5mz9TH';
    }
};

export default getSiteKey(window.location.hostname);
