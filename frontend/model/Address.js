import {Record} from 'immutable';

/**
 * Advocate address
 * @property {string} street
 * @property {string} city
 * @property {string} postcode
 */
const Address = Record({
    street: '',
    city: '',
    postcode: '',
});
export default Address;
