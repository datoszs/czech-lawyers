import {connect} from 'react-redux';
import {PageTitle} from '..';
import translate from '../../translate';

const mapStateToProps = (state, {msg, module}) => {
    const query = module.getQuery(state);
    if (query) {
        const title = translate.getMessage(state, msg);
        return {
            msg: 'search.title',
            title,
            query,
        };
    } else {
        return {msg};
    }
};

export default connect(mapStateToProps)(PageTitle);
