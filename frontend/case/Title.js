import {connect} from 'react-redux';
import {PageTitle} from '../containers';
import {getDetail} from './selectors';

const mapStateToProps = (state) => {
    const detail = getDetail(state);
    if (detail) {
        return {children: detail.registry};
    } else {
        return {msg: 'case.detail.title'};
    }
};

export default connect(mapStateToProps)(PageTitle);
