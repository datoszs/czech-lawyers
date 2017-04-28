import {expect} from 'chai';
import {List} from 'immutable';
import CaseDetail, {mapDtoToCaseDetail} from './CaseDetail';
import court from './courts';
import result from './result';
import {mapDtoToDocument} from './Document';

describe('Case Detail model', () => {
    const createTemplate = (custom = {}) => Object.assign({}, {
        id: 1,
        documents: [],
    }, custom);
    it('takes existing court', () => {
        const template = createTemplate({court: court.US});
        const caseDetail = new CaseDetail(template);
        caseDetail.court.should.equal(court.US);
    });
    it('marks unknown court as empty', () => {
        const template = createTemplate({court: 'UNKNOWN'});
        const caseDetail = new CaseDetail(template);
        expect(caseDetail.court).to.be.null();
    });
    it('takes existing result', () => {
        const template = createTemplate({result: result.POSITIVE});
        const caseDetail = new CaseDetail(template);
        caseDetail.result.should.equal(result.POSITIVE);
    });
    it('marks unknown result as empty', () => {
        const template = createTemplate({result: 'UNKNOWN'});
        const caseDetail = new CaseDetail(template);
        expect(caseDetail.result).to.be.null();
    });
    it('creates list from documents', () => {
        const caseDetail = new CaseDetail(createTemplate());
        caseDetail.documents.should.be.an.instanceOf(List);
    });
    it('maps documents into their ids', () => {
        const template = createTemplate({documents: [{
            id: 94001,
            mark: 'ECLI:CZ:NS:2010:20.CDO.2696.2010.1',
            date: 1490347289000,
            link: 'http://nsoud.cz/Judikatura/judikatura_ns.nsf/WebPrint/06FEB14C62D9D3B9C1257A4E0065FDFD?openDocument',
        }]});
        const caseDetail = new CaseDetail(template);
        caseDetail.documents.should.deep.equal(List.of(94001));
    });
    describe('DTO to Case Detail mapping', () => {
        const dto = {
            id_case: 93299,
            id_court: 2,
            registry_mark: '20 CDO 2696/2010',
            tagging_advocate: {id_advocate: 121, fullname: 'JUDr. Tomáš Sokol'},
            tagging_result: 'negative',
            documents: [{
                id_document: 94001,
                mark: 'ECLI:CZ:NS:2010:20.CDO.2696.2010.1',
                decision_date: '2010-08-17T02:00:00+02:00',
                public_link: 'http://nsoud.cz/Judikatura/judikatura_ns.nsf/WebPrint/06FEB14C62D9D3B9C1257A4E0065FDFD?openDocument',
            }],
        };
        it('creates valid object', () => {
            const caseDetail = new CaseDetail(mapDtoToCaseDetail(dto));
            caseDetail.should.be.an.instanceOf(CaseDetail);
        });
        const caseDetail = new CaseDetail(mapDtoToCaseDetail(dto));
        it('maps id', () => {
            caseDetail.id.should.equal(dto.id_case);
        });
        it('maps court', () => {
            caseDetail.court.should.equal(dto.id_court);
        });
        it('maps registry mark', () => {
            caseDetail.registry.should.equal(dto.registry_mark);
        });
        it('maps advocate id', () => {
            caseDetail.advocateId.should.equal(dto.tagging_advocate.id_advocate);
        });
        it('maps advocate name', () => {
            caseDetail.advocateName.should.equal(dto.tagging_advocate.fullname);
        });
        it('maps result', () => {
            caseDetail.result.should.equal(dto.tagging_result);
        });
        it('maps documents with appropriate mapping function', () => {
            const caseTemplate = mapDtoToCaseDetail(dto);
            const document = mapDtoToDocument(dto.documents[0]);
            caseTemplate.documents[0].should.deep.equal(document);
        });
        it('maps advocate name and id to null if there is no advocate', () => {
            const sampleDto = Object.assign({}, dto, {tagging_advocate: null});
            const otherCaseDetail = new CaseDetail(mapDtoToCaseDetail(sampleDto));
            expect(otherCaseDetail.advocateName).to.be.null();
            expect(otherCaseDetail.advocateId).to.be.null();
        });
    });
});
