import 'babel-polyfill';
import {configure} from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import chai, {should} from 'chai';
import chaiImmutable from 'chai-immutable';
import dirtyChai from 'dirty-chai';
import sinonChai from 'sinon-chai';
import chaiEnzyme from 'chai-enzyme';

configure({adapter: new Adapter()});

chai.use(chaiImmutable);
chai.use(chaiEnzyme());
chai.use(dirtyChai);
chai.use(sinonChai);

should();
