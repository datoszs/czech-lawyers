import Advocate, {mapDtoToAdvocate} from './Advocate';
import Address from './Address';
import Statistics from './Statistics';
import status from './status';

describe('Advocate model', () => {
    const createTemplate = (custom) => Object.assign({}, {
        id: 1,
    }, custom);
    it('takes existing address if given', () => {
        const template = createTemplate({
            address: new Address({}),
        });
        const advocate = new Advocate(template);
        advocate.address.should.equal(template.address);
    });
    it('takes existing statistics if given', () => {
        const template = createTemplate({
            statistics: new Statistics({}),
        });
        const advocate = new Advocate(template);
        advocate.statistics.should.equal(template.statistics);
    });
    it('marks unknown status as active', () => {
        const template = createTemplate({
            status: 'UNKNOWN',
        });
        const advocate = new Advocate(template);
        advocate.status.should.equal(status.ACTIVE);
    });
    it('marks empty status as active', () => {
        const advocate = new Advocate(createTemplate());
        advocate.status.should.equal(status.ACTIVE);
    });
    describe('DTO to Advocate mapping', () => {
        const dto = {
            id_advocate: 123,
            identification_number: '11223344',
            fullname: 'JUDr. Ing. Petr Omáčka, PhD.',
            residence: {
                street: 'Pod mostem',
                city: 'Brno',
                postal_area: '602 00',
            },
            state: 'active',
            matched: {
                type: 'ic',
                value: '11223344',
            },
            statistics: {
                negative: 12,
                neutral: 2,
                positive: 59,
            },
        };
        it('creates valid object', () => {
            const advocate = new Advocate(mapDtoToAdvocate(dto));
            advocate.should.be.an.instanceOf(Advocate);
        });
        const advocate = new Advocate(mapDtoToAdvocate(dto));
        it('maps id', () => {
            advocate.id.should.equal(dto.id_advocate);
        });
        it('maps IČ', () => {
            advocate.ic.should.equal(dto.identification_number);
        });
        it('maps name', () => {
            advocate.name.should.equal(dto.fullname);
        });
        it('maps status', () => {
            advocate.status.should.equal(dto.state);
        });
        it('creates address object', () => {
            advocate.address.should.be.an.instanceOf(Address);
        });
        it('maps address street', () => {
            advocate.address.street.should.equal(dto.residence.street);
        });
        it('maps address city', () => {
            advocate.address.city.should.equal(dto.residence.city);
        });
        it('maps address postcode', () => {
            advocate.address.postcode.should.equal(dto.residence.postal_area);
        });
        it('creates statistics object', () => {
            advocate.statistics.should.be.an.instanceOf(Statistics);
        });
        it('maps statistics positive', () => {
            advocate.statistics.positive.should.equal(dto.statistics.positive);
        });
        it('maps statistics negative', () => {
            advocate.statistics.negative.should.equal(dto.statistics.negative);
        });
        it('maps statistics neutral', () => {
            advocate.statistics.neutral.should.equal(dto.statistics.neutral);
        });
    });
});
