import 'babel-polyfill';
import chai, {should} from 'chai';
import chaiImmutable from 'chai-immutable';
import dirtyChai from 'dirty-chai';
import sinonChai from 'sinon-chai';
import chaiEnzyme from 'chai-enzyme';

chai.use(chaiImmutable);
chai.use(chaiEnzyme());
chai.use(dirtyChai);
chai.use(sinonChai);

should();
