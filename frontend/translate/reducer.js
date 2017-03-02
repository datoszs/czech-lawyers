import {fromJS} from 'immutable';
import messages from './cs';

export default (state = fromJS(messages)) => state;
