import {connect} from 'react-redux';
import {reduxForm} from 'redux-form/immutable';
import translate from '../translate';
import router from '../router';
import {CASE_SEARCH} from '../routes';
import {SEARCH_FORM} from './constants';
import SearchComponent from './SearchComponent';

const mapStateToProps = (state) => ({
    msgSearch: translate.getMessage(state, 'search.button'),
    msgPlaceholder: translate.getMessage(state, 'cases.search.placeholder'),
});

const onSubmit = (values, dispatch) => dispatch(router.transition(CASE_SEARCH, undefined, values.toJS()));

export default reduxForm({
    form: SEARCH_FORM,
    onSubmit,
})(connect(mapStateToProps)(SearchComponent));
