import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {Msg} from '../containers';
import {getDocuments} from './selectors';
import DocumentDetail from './DocumentDetail';

const DocumentContainer = ({documents}) => (
    <section>
        <header><h1><Msg msg="case.documents" /></h1></header>
        {documents.map((document) => <DocumentDetail key={document} id={document} />)}
    </section>
);

DocumentContainer.propTypes = {
    documents: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    documents: getDocuments(state),
});

export default connect(mapStateToProps)(DocumentContainer);
