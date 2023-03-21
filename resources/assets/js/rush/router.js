import Rush from './views/widgets/Rush'
import RushWizard from './views/widgets/RushWizard'
import RushView from './views/widgets/RushView'

let routes = [
    {
        path: '/',
        name: 'rush',
        component: Rush,
    },
    {
        path: '/add',
        name: 'rush.add',
        component: RushWizard,
        props: true,
    },
    {
        path: '/edit/:rushId',
        name: 'rush.edit',
        component: RushWizard,
        props: true,
    },
    {
        path: '/:rushId',
        name: 'rush.view',
        component: RushView,
        props: true,
    },
    {
        path: '/favorite/:rushId',
        name: 'rush.favorite',
        component: RushView,
        props: true,
    },
]

const router = new VueRouter({
    routes,
    base: '/rush',
    mode: 'history',
})

export default router