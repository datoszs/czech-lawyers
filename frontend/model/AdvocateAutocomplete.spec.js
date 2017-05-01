import AdvocateAutocomplete, {mapDtoToAdvocateAutocomplete} from './AdvocateAutocomplete';

describe('DTO to Advocate Autocomplete mapping', () => {
    const dto = {
        id_advocate: 123,
        fullname: 'JUDr. Ing. Petr Omáčka, PhD.',
        matched: {
            type: 'ic',
            value: '11223344',
        },
    };
    it('creates valid object', () => {
        const advocate = new AdvocateAutocomplete(mapDtoToAdvocateAutocomplete(dto));
        advocate.should.be.an.instanceOf(AdvocateAutocomplete);
    });
    const advocate = new AdvocateAutocomplete(mapDtoToAdvocateAutocomplete(dto));
    it('maps id', () => {
        advocate.id.should.equal(dto.id_advocate);
    });
    it('maps advocate name', () => {
        advocate.name.should.equal(dto.fullname);
    });
    it('maps match value', () => {
        advocate.matched.should.equal(dto.matched.value);
    });
});
