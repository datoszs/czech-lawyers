import {connect} from 'react-redux';
import {PageHeader} from 'react-bootstrap';
import translate from '../translate';
import {getDetail} from './selectors';

const mapStateToProps = (state) => {
    const caseDetail = getDetail(state);
    if (caseDetail) {
        return {children: caseDetail.registry};
    } else {
        return {children: translate.getMessage(state, 'case.detail.title')};
    }
};

const mergeProps = ({children}) => ({children});

export default connect(mapStateToProps, undefined, mergeProps)(PageHeader);

