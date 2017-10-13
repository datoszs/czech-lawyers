import {connect} from 'react-redux';
import {Statement} from '../components/statement';
import router from '../router';
import {STATEMENTS} from '../routes';

const mapStateToProps = (state, {anchor}) => ({
    href: router.getHref(state, STATEMENTS, undefined, undefined, anchor),
});

const mapDispatchToProps = (dispatch, {anchor}) => ({
    onClick: () => dispatch(router.transition(STATEMENTS, undefined, undefined, anchor)),
});

export default connect(mapStateToProps, mapDispatchToProps)(Statement);
