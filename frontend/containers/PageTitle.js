import {connect} from 'react-redux';
import {PageTitle} from '../components';
import translate from '../translate';

const mapStateToProps = (state, {msg, children, ...params}) => {
    const page = msg ? translate.getMessage(state, msg, params) : children;
    return {children: translate.getMessage(state, 'title.base', {page})};
};

export default connect(mapStateToProps)(PageTitle);
