import Address from './Address';
import Statistics from './Statistics';
import AdvocateDetail, {mapDtoToAdvocateDetail} from './AdvocateDetail';
import status from './status';

describe('Advocate Detail model', () => {
    const createTemplate = (custom) => Object.assign({}, {
        id: 1,
    }, custom);
    it('takes existing address if given', () => {
        const template = createTemplate({
            address: new Address({}),
        });
        const advocate = new AdvocateDetail(template);
        advocate.address.should.equal(template.address);
    });
    it('takes existing statistics if given', () => {
        const template = createTemplate({
            statistics: new Statistics({}),
        });
        const advocate = new AdvocateDetail(template);
        advocate.statistics.should.equal(template.statistics);
    });
    it('takes existing status', () => {
        const template = createTemplate({
            status: status.SUSPENDED,
        });
        const advocate = new AdvocateDetail(template);
        advocate.status.should.equal(status.SUSPENDED);
    });
    it('marks unknown status as active', () => {
        const template = createTemplate({
            status: 'UNKNOWN',
        });
        const advocate = new AdvocateDetail(template);
        advocate.status.should.equal(status.ACTIVE);
    });
    it('marks empty status as active', () => {
        const advocate = new AdvocateDetail(createTemplate());
        advocate.status.should.equal(status.ACTIVE);
    });
    describe('DTO to Advocate Detail mapping', () => {
        const dto = {
            id_advocate: 123,
            remote_identificator: '77b3dbfb-f855-4170-9d5b-dc30757a0204',
            identification_number: '11223344',
            registration_number: '00001',
            fullname: 'JUDr. Ing. Petr Omáčka, PhD.',
            residence: {
                street: 'Pod mostem',
                city: 'Brno',
                postal_area: '602 00',
            },
            emails: [
                'petr.omacka@example.com',
            ],
            state: 'active',
            remote_page: 'http://vyhledavac.cak.cz/Units/_Search/Details/detailAdvokat.aspx?id=77b3dbfb-f855-4170-9d5b-dc30757a0204',
            statistics: {
                negative: 12,
                neutral: 2,
                positive: 59,
            },
        };
        it('creates valid object', () => {
            const advocate = new AdvocateDetail(mapDtoToAdvocateDetail(dto));
            advocate.should.be.an.instanceof(AdvocateDetail);
        });
        const advocate = new AdvocateDetail((mapDtoToAdvocateDetail(dto)));
        it('maps id', () => {
            advocate.id.should.equal(dto.id_advocate);
        });
        it('maps IČ', () => {
            advocate.ic.should.equal(dto.identification_number);
        });
        it('maps remote identifier', () => {
            advocate.remoteId.should.equal(dto.remote_identificator);
        });
        it('maps registration number', () => {
            advocate.registrationNumber.should.equal(dto.registration_number);
        });
        it('maps name', () => {
            advocate.name.should.equal(dto.fullname);
        });
        it('maps status', () => {
            advocate.status.should.equal(dto.state);
        });
        it('maps remote URL', () => {
            advocate.remoteUrl.should.equal(dto.remote_page);
        });
        it('maps address', () => {
            advocate.address.should.be.an.instanceof(Address);
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
        it('maps statistics', () => {
            advocate.statistics.should.be.an.instanceof(Statistics);
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
