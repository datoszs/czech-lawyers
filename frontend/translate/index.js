import {NAME} from './constants';
import {getMessage} from './selectors';
import reducer from './reducer';

/**
 * INTERNATIONALIZATION MODULE
 *
 * Stores messages and exposes them via selectors.
 *
 * There is no support for switching yet, but it should be quite simple to add.
 *
 */
const translate = {NAME, getMessage, reducer};
export default translate;
