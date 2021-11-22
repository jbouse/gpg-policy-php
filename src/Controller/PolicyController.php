<?php
// src/Controller/PolicyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/", name="gpg_")
 */
class PolicyController extends AbstractController
{
    protected function get_current(): int
    {
        $dataDir = $this->getParameter('app.data_dir');
        $prefix = $this->getParameter('app.policy_prefix');

        $finder = new Finder();
        $finder->files()
                ->in($dataDir)
                ->name('current')
                ->depth('== 0');
        
        if ($finder->hasResults()) {
            foreach ($finder as $current) {
                return trim($current->getContents());
            }
        }

        $pattern = join('\.', array($prefix, '([0-9]{8})'));
        $finder = new Finder();
        $finder->files()
                ->in($dataDir)
                ->name('/^' . $pattern . '$/')
                ->sortByName(true)
                ->depth('== 0')
                ->reverseSorting();

        if ($finder->hasResults()) {
            $current = $finder->getIterator()->current();
            if(preg_match('/^'.$pattern.'$/', $current->getRelativePathname(), $matches)) {
                return $matches[1];
            }
        }
    }

    protected function get_policy(int $policyId): object
    {
        $dataDir = $this->getParameter('app.data_dir');
        $prefix = $this->getParameter('app.policy_prefix');

        $pattern = join('\.', array($prefix, $policyId));
        $finder = new Finder();
        $finder->files()
                ->in($dataDir)
                ->name('/^' . $pattern . '$/')
                ->depth('== 0');
        
        if ($finder->hasResults()) {
            foreach ($finder as $current) {
                return $current;
            }
        }

        throw $this->createNotFoundException('Not a valid GNU Privacy Guard Policy');
    }

    protected function get_signature(int $policyId, string $keyId): object
    {
        $dataDir = $this->getParameter('app.data_dir');
        $prefix = $this->getParameter('app.policy_prefix');

        $pattern = join('\.', array($prefix, $policyId, $keyId, 'sig'));
        $finder = new Finder();
        $finder->files()
                ->in($dataDir)
                ->name('/^' . $pattern . '$/')
                ->depth('== 0');
        
        if ($finder->hasResults()) {
            foreach ($finder as $current) {
                return $current;
            }
        }

        throw $this->createNotFoundException('Not a valid GNU Privacy Guard Policy Signature');
    }

    protected function get_signatures(int $policyId): array
    {
        $dataDir = $this->getParameter('app.data_dir');
        $prefix = $this->getParameter('app.policy_prefix');
        $signatures = [];

        $finder = new Finder();
        $finder->files()
                ->in($dataDir)
                ->name($prefix . '.' . $policyId . '*.sig')
                ->depth('== 0')
                ->sortByName();
        
        if ($finder->hasResults()) {
            $pattern = join('\.', array($prefix, $policyId, '([0-9A-Fa-f]{8})', 'sig'));
            foreach ($finder as $current) {
                if(preg_match('/^'.$pattern.'$/', $current->getRelativePathname(), $matches)) {
                    $signatures[] = $matches[1];
                }
            }
        }

        return $signatures;
    }

    protected function validate_checksum(int $policyId, string $checksum, string $algo): void
    {
        $policy = $this->get_policy($policyId);
        if (hash_file($algo, $policy->getRealPath()) == $checksum) {
            $this->addFlash(
                'success',
                strtoupper($algo) . ' Checksum valid'
            );    
        } else {
            $this->addFlash(
                'warning',
                strtoupper($algo) . ' Checksum NOT valid'
            );
        }
    }

    /**
     * @Route("/", name="policy_index")
     */
    public function index(): Response
    {
        $url = $this->generateUrl('gpg_policy_show', ['policyId' => $this->get_current()]);

        return new RedirectResponse($url);
    }

    /**
     * @Route("/download/{policyId<[0-9]{8}>}", name="policy_download")
     */
    public function policy_download(int $policyId): Response
    {
        $file = new File($this->get_policy($policyId));

        return $this->file($file, join('-', array('gpg', 'policy', $policyId)));
    }

    /**
     * @Route("/signature/{policyId<[0-9]{8}>}/{keyId<[0-9A-Fa-f]{8}>}", name="signature_download")
     */
    public function signature_download(int $policyId, string $keyId): Response
    {
        $file = new File($this->get_signature($policyId, $keyId));

        return $this->file($file, join('-', array('gpg', 'policy', $policyId, $keyId)) . '.asc' );
    }

    /**
     * @Route("/policy/{policyId<[0-9]{8}>}", name="policy_show")
     */
    public function show(int $policyId): Response
    {
        $policy = $this->get_policy($policyId);

        return $this->render('policy/show.html.twig', [
            'policyId' => $policyId,
            'policy' => $policy->getContents(),
            'signatures' => $this->get_signatures($policyId),
        ]);
    }

    /**
     * @Route("/policy/{policyId<[0-9]{8}>}/{checksum<[0-9A-Fa-f]{32}>}", name="policy_md5sum")
     */
    public function md5sum(int $policyId, string $checksum): Response
    {
        $this->validate_checksum($policyId, $checksum, 'md5');

        $url = $this->generatgeUrl('gpg_policy_show', ['policyId' => $policyId]);

        return new RedirectResponse($url);
    }

    /**
     * @Route("/policy/{policyId<[0-9]{8}>}/{checksum<[0-9A-Fa-f]{40}>}", name="policy_sha1sum")
     */
    public function sha1sum(int $policyId, string $checksum): Response
    {
        $this->validate_checksum($policyId, $checksum, 'sha1');

        $url = $this->generateUrl('gpg_policy_show', ['policyId' => $policyId]);

        return new RedirectResponse($url);
    }

    /**
     * @Route("/policy/{policyId<[0-9]{8}>}/{checksum<[0-9A-Fa-f]{64}>}", name="policy_sha256sum")
     */
    public function sha256sum(int $policyId, string $checksum): Response
    {
        $this->validate_checksum($policyId, $checksum, 'sha256');

        $url = $this->generateUrl('gpg_policy_show', ['policyId' => $policyId]);

        return new RedirectResponse($url);
    }
}