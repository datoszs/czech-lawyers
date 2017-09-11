import invariant from 'invariant';
import {Record, List} from 'immutable';
import status, {checkStatus} from './status';
import Address from './Address';
import Statistics from './Statistics';
import {getOrCreate} from './util';

/**
 * Advocate detail
 * @property {number} id
 * @property {string} remoteId
 * @property {string} ic
 * @property {string} registrationNumber
 * @property {string} name
 * @property {Address} address
 * @property {string[]} emails
 * @property {string} status
 * @property {string} remoteUrl
 * @property {Statistics} statistics
 */
class AdvocateDetail extends Record({
    id: 0,
    remoteId: null,
    ic: '',
    registrationNumber: '',
    name: '',
    address: null,
    emails: [],
    status: status.ACTIVE,
    remoteUrl: null,
    statistics: null,
}) {
    constructor(values) {
        invariant(values.id, 'Advocate id must be specified.');
        super(Object.assign({}, values, {
            address: getOrCreate(Address, values.address),
            statistics: getOrCreate(Statistics, values.statistics),
            emails: List(values.emails),
            status: checkStatus(values.status),
        }));
    }

    get active() {
        return this.status !== status.REMOVED;
    }
}

export const mapDtoToAdvocateDetail = (dto) => ({
    id: dto.id_advocate,
    ic: dto.identification_number,
    name: dto.fullname,
    status: dto.state,
    registrationNumber: dto.registration_number,
    emails: dto.emails,
    remoteId: dto.remote_identificator,
    remoteUrl: dto.remote_page,
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

export default AdvocateDetail;
