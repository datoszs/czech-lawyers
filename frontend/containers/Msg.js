import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Text} from '../components';
import translate from '../translate';

const mapStateToProps = (state, {msg, ...params}) => ({
    text: translate.getMessage(state, msg, params),
});

/**
 * Prints translated message.
 * @param msg Message key.
 * @param params Parameters object (optional).
 */
const Msg = connect(mapStateToProps)(Text);

Msg.propTypes = {
    msg: PropTypes.string.isRequired,
};

export default Msg;
