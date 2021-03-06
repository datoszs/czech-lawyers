/**
 * @property NS Nejvyšší soud (Supreme Court)
 * @property NSS Nejvyšší správní soud (Supreme Administrative Court)
 * @property US Ústavní soud (Constitutional Court)
 */
const courts = {
    NSS: 1,
    NS: 2,
    US: 3,
};

export const courtsMsg = {
    [courts.NS]: 'court.ns',
    [courts.NSS]: 'court.nss',
    [courts.US]: 'court.us',
};

export const checkCourt = (value) => (Object.values(courts).includes(value) ? value : null);

export default courts;
