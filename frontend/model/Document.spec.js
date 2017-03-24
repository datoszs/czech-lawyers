import Document, {mapDtoToDocument} from './Document';

describe('DTO to Document mapping', () => {
    const dto = {
        id_document: 94001,
        mark: 'ECLI:CZ:NS:2010:20.CDO.2696.2010.1',
        decision_date: '2010-08-17T02:00:00+02:00',
        public_link: 'http://nsoud.cz/Judikatura/judikatura_ns.nsf/WebPrint/06FEB14C62D9D3B9C1257A4E0065FDFD?openDocument',
    };
    it('creates valid object', () => {
        const document = new Document(mapDtoToDocument(dto));
        document.should.be.an.instanceOf(Document);
    });
    const document = new Document(mapDtoToDocument(dto));
    it('maps id', () => {
        document.id.should.equal(dto.id_document);
    });
    it('maps mark', () => {
        document.mark.should.equal(dto.mark);
    });
    it('converts date', () => {
        document.date.should.equal(1282003200000);
    });
    it('maps link', () => {
        document.link.should.equal(dto.public_link);
    });
});
