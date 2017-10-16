import {connect} from 'react-redux';
import {Button} from 'react-bootstrap';
import {If, wrapLinkMouseEvent} from '../../util';
import translate from '../../translate';
import router from '../../router';
import {CASE_DETAIL} from '../../routes';
import {getCaseId, isLoading} from './selectors';

const mapStateToProps = (state) => {
    const caseId = getCaseId(state);
    return ({
        caseId,
        children: translate.getMessage(state, 'case.dispute.verify.case'),
        disabled: isLoading(state),
        href: router.getHref(state, CASE_DETAIL, {id: caseId}),
    });
};

const mapDispatchToProps = (dispatch) => ({
    transition: (id) => () => dispatch(router.transition(CASE_DETAIL, {id})),
});

const mergeProps = ({caseId, disabled, children, href}, {transition}) => ({
    test: !!caseId,
    Component: Button,
    onClick: wrapLinkMouseEvent(transition(caseId)),
    disabled,
    children,
    href,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(If);

