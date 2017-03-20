import {PropTypes} from 'react';
import {connect} from 'react-redux';
import translate from '../translate';
import {DetailFieldComponent} from '../components';

const mapStateToProps = (state, {msg}) => ({
    label: translate.getMessage(state, msg),
});

const DetailField = connect(mapStateToProps)(DetailFieldComponent);

DetailField.propTypes = {
    msg: PropTypes.string.isRequired,
    children: PropTypes.node,
};

DetailField.defaultProps = {
    children: null,
};

export default DetailField;
