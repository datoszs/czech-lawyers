import PropTypes from 'prop-types';

export default PropTypes.shape({
    name: PropTypes.string.isRequired,
    required: PropTypes.bool.isRequired,
}).isRequired;
