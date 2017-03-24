import {expect} from 'chai';
import Case, {mapDtoToCase} from './Case';
import result from './result';
import courts from './courts';

describe('Case model', () => {
    const createTemplate = (custom) => Object.assign({}, {
        id: 1,
    }, custom);
    it('takes existing result', () => {
        const template = createTemplate({
            result: result.POSITIVE,
        });
        const caseObj = new Case(template);
        caseObj.result.should.equal(result.POSITIVE);
    });
    it('removes unknown result', () => {
        const template = createTemplate({
            result: 'UNKNOWN',
        });
        const caseObj = new Case(template);
        expect(caseObj.result).to.equal(null);
    });
    it('takes existing court', () => {
        const template = createTemplate({
            court: courts.US,
        });
        const caseObj = new Case(template);
        caseObj.court.should.equal(courts.US);
    });
    it('removes unknown court', () => {
        const template = createTemplate({
            court: 'UNKNOWN',
        });
        const caseObj = new Case(template);
        expect(caseObj.court).to.equal(null);
    });

    describe('DTO to Case mapping', () => {
        const dto = {
            id_case: 25,
            id_court: 2,
            registry_mark: '42 CDO 4000/2016',
            result: 'negative',
        };
        it('creates valid object', () => {
            const caseObj = new Case(mapDtoToCase(dto));
            caseObj.should.be.an.instanceOf(Case);
        });
        const caseObj = new Case(mapDtoToCase(dto));
        it('maps id', () => {
            caseObj.id.should.equal(dto.id_case);
        });
        it('maps court', () => {
            caseObj.court.should.equal(dto.id_court);
        });
        it('maps registry mark', () => {
            caseObj.registry.should.equal(dto.registry_mark);
        });
        it('maps result', () => {
            caseObj.result.should.equal(dto.result);
        });
    });
});
