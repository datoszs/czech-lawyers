import {connect} from 'react-redux';
import {PageTitle} from '../components';
import translate from '../translate';

const mapStateToProps = (state, {msg, params}) => {
    const page = translate.getMessage(state, msg, params);
    return {children: translate.getMessage(state, 'title.base', {page})};
};

export default connect(mapStateToProps)(PageTitle);
