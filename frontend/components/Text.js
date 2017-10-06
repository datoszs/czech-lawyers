import PropTypes from 'prop-types';

/**
 * Simple text component.
 */
const Text = ({text}) => text;

Text.propTypes = {
    text: PropTypes.string.isRequired,
};

export default Text;
