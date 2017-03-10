import invariant from 'invariant';
import {Record} from 'immutable';
import status, {checkStatus} from './status';
import Address from './Address';
import Statistics from './Statistics';
import {getOrCreate} from './util';

/**
 * Advocate
 * @property {number} id
 * @property {string} ic
 * @property {string} name
 * @property {string} status
 * @property {Address} address
 * @property {Statistics} statistics
 */
class Advocate extends Record({
    id: 0,
    ic: '',
    name: '',
    status: status.ACTIVE,
    address: null,
    statistics: null,
}) {
    constructor(values) {
        invariant(values.id, 'Advocate id must be specified.');
        super(Object.assign({}, values, {
            address: getOrCreate(Address, values.address),
            statistics: getOrCreate(Statistics, values.statistics),
            status: checkStatus(values.status),
        }));
    }
}

export const mapDtoToAdvocate = (dto) => ({
    id: dto.id_advocate,
    ic: dto.identification_number,
    name: dto.fullname,
    status: dto.state,
    address: {
        street: dto.residence.street,
        city: dto.residence.city,
        postcode: dto.residence.postal_area,
    },
    statistics: {
        positive: dto.statistics.positive,
        negative: dto.statistics.negative,
        neutral: dto.statistics.neutral,
    },
});

export default Advocate;
