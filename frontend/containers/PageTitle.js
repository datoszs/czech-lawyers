import {connect} from 'react-redux';
import PropTypes from 'prop-types';
import {PageTitle as PageTitleComponent} from '../components';
import translate from '../translate';

const mapStateToProps = (state, {msg, children, ...params}) => {
    const page = msg ? translate.getMessage(state, msg, params) : children;
    return {children: translate.getMessage(state, 'title.base', {page})};
};

const PageTitle = connect(mapStateToProps)(PageTitleComponent);

PageTitle.propTypes = {
    msg: PropTypes.string,
    children: PropTypes.string,
};

PageTitle.defaultProps = {
    msg: null,
    children: '',
};

export default PageTitle;
