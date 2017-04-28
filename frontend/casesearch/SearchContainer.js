import {connect} from 'react-redux';
import {reduxForm} from 'redux-form/immutable';
import translate from '../translate';
import {transition} from '../util';
import {SEARCH_FORM, ROUTE} from './constants';
import SearchComponent from './SearchComponent';

const mapStateToProps = (state) => ({
    msgSearch: translate.getMessage(state, 'search.button'),
    msgPlaceholder: translate.getMessage(state, 'cases.search.placeholder'),
});

const onSubmit = (values) => transition(ROUTE, undefined, values.toJS());

export default reduxForm({
    form: SEARCH_FORM,
    onSubmit,
})(connect(mapStateToProps)(SearchComponent));
